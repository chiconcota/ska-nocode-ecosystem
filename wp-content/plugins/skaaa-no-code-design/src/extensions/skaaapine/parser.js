import { __ } from '@wordpress/i18n';
import jsep from 'jsep';
import assignment from '@jsep-plugin/assignment';

jsep.plugins.register(assignment);

/**
 * An toàn parse chuỗi x-data thành object ({ open: false } -> obj)
 */
export function parseInitialState(xDataString) {
    if (!xDataString) return {};
    try {
        const fn = new Function('return ' + xDataString);
        const res = fn();
        return typeof res === 'object' && res !== null ? res : {};
    } catch (err) {
        console.error(__( 'Skaaapine: Error parsing x-data:', 'skaaa-no-code-design' ), xDataString, err);
        return {};
    }
}

/**
 * Đánh giá biểu thức AST sử dụng jsep. 
 * Tự động trả về object state cập nhật nếu biểu thức là Assignment.
 */
export function evaluateExpression(exprString, stateContext) {
    if (!exprString) return undefined;
    
    try {
        const ast = jsep(exprString);
        return evaluateAst(ast, stateContext);
    } catch (err) {
        console.error(__( 'Skaaapine: AST parse error:', 'skaaa-no-code-design' ), exprString, err);
        return undefined;
    }
}

function evaluateAst(node, context) {
    switch (node.type) {
        case 'Identifier':
            return context[node.name];
        case 'Literal':
            return node.value;
        case 'BinaryExpression': {
            const left = evaluateAst(node.left, context);
            const right = evaluateAst(node.right, context);
            switch (node.operator) {
                case '===': return left === right;
                case '!==': return left !== right;
                case '==': return left == right;
                case '!=': return left != right;
                case '>': return left > right;
                case '<': return left < right;
                case '>=': return left >= right;
                case '<=': return left <= right;
                case '+': return left + right;
                case '-': return left - right;
                case '*': return left * right;
                case '/': return left / right;
                default:
                    throw new Error(`Toán tử chưa được hỗ trợ: ${node.operator}`);
            }
        }
        case 'ConditionalExpression': {
            const test = evaluateAst(node.test, context);
            if (test) {
                return evaluateAst(node.consequent, context);
            } else {
                return evaluateAst(node.alternate, context);
            }
        }
        case 'LogicalExpression': {
            const left = evaluateAst(node.left, context);
            if (node.operator === '&&') return left && evaluateAst(node.right, context);
            if (node.operator === '||') return left || evaluateAst(node.right, context);
            throw new Error(`Toán tử logic chưa hỗ trợ: ${node.operator}`);
        }
        case 'UnaryExpression': {
            const arg = evaluateAst(node.argument, context);
            if (node.operator === '!') return !arg;
            if (node.operator === '-') return -arg;
            if (node.operator === '+') return +arg;
            throw new Error(`Toán tử đơn chưa hỗ trợ: ${node.operator}`);
        }
        case 'AssignmentExpression': {
            // VD: open = !open. Trả về { open: newState } để setState merge lại
            if (node.operator === '=') {
                const newValue = evaluateAst(node.right, context);
                
                if (node.left.type === 'Identifier') {
                    return { [node.left.name]: newValue };
                }
                else if (node.left.type === 'MemberExpression') {
                    const obj = evaluateAst(node.left.object, context);
                    if (obj) {
                        const prop = node.left.computed ? evaluateAst(node.left.property, context) : node.left.property.name;
                        obj[prop] = newValue;
                        return { type: 'DEEP_UPDATE' };
                    }
                }
            }
            throw new Error(`Chỉ hỗ trợ gán (Assignment) cho biến cơ bản (Identifier) và thuộc tính (MemberExpression)`);
        }
        case 'MemberExpression': {
            const obj = evaluateAst(node.object, context);
            if (!obj) return undefined;
            const prop = node.computed ? evaluateAst(node.property, context) : node.property.name;
            return obj[prop];
        }
        case 'CallExpression': {
            const callee = evaluateAst(node.callee, context);
            const args = node.arguments.map(arg => evaluateAst(arg, context));
            if (typeof callee === 'function') {
                // If it's a member expression, we need the context (this binding)
                if (node.callee.type === 'MemberExpression') {
                    const obj = evaluateAst(node.callee.object, context);
                    return callee.apply(obj, args);
                }
                return callee.apply(null, args);
            }
            throw new Error(`Cannot call non-function`);
        }
        default:
            throw new Error(`Cú pháp chưa hỗ trợ: ${node.type}`);
    }
}

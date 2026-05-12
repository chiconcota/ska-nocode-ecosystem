export const TAILWIND_CATEGORIES = [
    {
        id: 'layout',
        title: 'Layout & Display',
        classes: [
            'block', 'inline-block', 'inline', 'flex', 'inline-flex', 'grid', 'inline-grid', 'hidden',
            'flex-row', 'flex-col', 'flex-wrap', 'flex-nowrap',
            'justify-start', 'justify-end', 'justify-center', 'justify-between', 'justify-around',
            'items-start', 'items-end', 'items-center', 'items-baseline', 'items-stretch',
            'content-center', 'content-start', 'content-end', 'content-between',
            'grid-cols-1', 'grid-cols-2', 'grid-cols-3', 'grid-cols-4', 'grid-cols-6', 'grid-cols-12',
            'col-span-1', 'col-span-2', 'col-span-3', 'col-span-4', 'col-span-6', 'col-span-12', 'col-span-full'
        ]
    },
    {
        id: 'position',
        title: 'Position',
        classes: [
            'static', 'fixed', 'absolute', 'relative', 'sticky',
            'inset-0', 'inset-x-0', 'inset-y-0',
            'top-0', 'right-0', 'bottom-0', 'left-0',
            'z-0', 'z-10', 'z-20', 'z-30', 'z-40', 'z-50', 'z-auto'
        ]
    },
    {
        id: 'spacing',
        title: 'Spacing (Margin & Padding)',
        classes: [
            // Padding
            'p-0', 'p-1', 'p-2', 'p-3', 'p-4', 'p-6', 'p-8', 'p-12',
            'px-0', 'px-2', 'px-4', 'px-6', 'px-8',
            'py-0', 'py-2', 'py-4', 'py-6', 'py-8',
            'pt-0', 'pt-2', 'pt-4', 'pt-8', 'pb-0', 'pb-2', 'pb-4', 'pb-8',
            'pl-0', 'pl-2', 'pl-4', 'pl-8', 'pr-0', 'pr-2', 'pr-4', 'pr-8',
            // Margin
            'm-0', 'm-1', 'm-2', 'm-3', 'm-4', 'm-6', 'm-8', 'm-12', 'm-auto',
            'mx-0', 'mx-2', 'mx-4', 'mx-6', 'mx-auto',
            'my-0', 'my-2', 'my-4', 'my-6', 'my-auto',
            'mt-0', 'mt-2', 'mt-4', 'mt-8', 'mb-0', 'mb-2', 'mb-4', 'mb-8',
            // Gap
            'gap-0', 'gap-1', 'gap-2', 'gap-4', 'gap-6', 'gap-8', 'gap-12'
        ]
    },
    {
        id: 'sizing',
        title: 'Sizing',
        classes: [
            'w-0', 'w-full', 'w-screen', 'w-auto', 'w-1/2', 'w-1/3', 'w-2/3', 'w-1/4', 'w-3/4',
            'max-w-xs', 'max-w-sm', 'max-w-md', 'max-w-lg', 'max-w-xl', 'max-w-2xl', 'max-w-full',
            'h-0', 'h-full', 'h-screen', 'h-auto',
            'min-h-0', 'min-h-full', 'min-h-screen'
        ]
    },
    {
        id: 'typography',
        title: 'Typography',
        classes: [
            'text-xs', 'text-sm', 'text-base', 'text-lg', 'text-xl', 'text-2xl', 'text-3xl', 'text-4xl',
            'font-thin', 'font-light', 'font-normal', 'font-medium', 'font-semibold', 'font-bold', 'font-black',
            'italic', 'not-italic',
            'text-left', 'text-center', 'text-right', 'text-justify',
            'uppercase', 'lowercase', 'capitalize', 'normal-case',
            'underline', 'line-through', 'no-underline',
            'leading-none', 'leading-tight', 'leading-snug', 'leading-normal', 'leading-relaxed', 'leading-loose'
        ]
    },
    {
        id: 'borders',
        title: 'Borders & Radius',
        classes: [
            'border-0', 'border', 'border-2', 'border-4', 'border-8',
            'border-t', 'border-r', 'border-b', 'border-l',
            'border-solid', 'border-dashed', 'border-dotted', 'border-none',
            'rounded-none', 'rounded-sm', 'rounded', 'rounded-md', 'rounded-lg', 'rounded-xl', 'rounded-2xl', 'rounded-3xl', 'rounded-full'
        ]
    },
    {
        id: 'effects',
        title: 'Effects & Filters',
        classes: [
            'shadow-sm', 'shadow', 'shadow-md', 'shadow-lg', 'shadow-xl', 'shadow-2xl', 'shadow-inner', 'shadow-none',
            'opacity-0', 'opacity-25', 'opacity-50', 'opacity-75', 'opacity-100',
            'mix-blend-normal', 'mix-blend-multiply', 'mix-blend-screen', 'mix-blend-overlay',
            'blur-none', 'blur-sm', 'blur', 'blur-md', 'blur-lg'
        ]
    },
    {
        id: 'transitions',
        title: 'Transitions & Transforms',
        classes: [
            'transition-none', 'transition-all', 'transition', 'transition-colors', 'transition-opacity', 'transition-shadow', 'transition-transform',
            'duration-75', 'duration-100', 'duration-150', 'duration-200', 'duration-300', 'duration-500', 'duration-700', 'duration-1000',
            'ease-linear', 'ease-in', 'ease-out', 'ease-in-out',
            'scale-0', 'scale-50', 'scale-75', 'scale-90', 'scale-95', 'scale-100', 'scale-105', 'scale-110', 'scale-125', 'scale-150',
            'group' // useful for group-hover
        ]
    }
];

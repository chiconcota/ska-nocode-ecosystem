<?php
/**
 * Context Manager Class
 *
 * Manages the data context stack for handling nested loops and dynamic data scope.
 *
 * @package Ska_Builder_Core
 */

namespace Ska\Builder\Data;

defined( 'ABSPATH' ) || exit;

/**
 * Class Context_Manager
 */
class Context_Manager {

	/**
	 * Stack of contexts.
	 *
	 * @var array
	 */
	private $stack = array();

	/**
	 * Constructor.
	 */
	public function __construct() {
		// Initialize with default global context.
		$this->reset_context();
	}

	/**
	 * Push a new context onto the stack.
	 *
	 * @param int    $id   Object ID (Post ID, User ID, Term ID).
	 * @param string $type Type of object ('post', 'user', 'term', 'option').
	 */
	public function push_context( $id, $type = 'post' ) {
		$this->stack[] = array(
			'id'   => $id,
			'type' => $type,
		);
	}

	/**
	 * Pop the current context off the stack.
	 *
	 * @return array|null The popped context or null if stack is empty (shouldn't happen if managed correctly).
	 */
	public function pop_context() {
		if ( count( $this->stack ) > 1 ) {
			return array_pop( $this->stack );
		}
		// Never pop the base context (Global/Main Query).
		return $this->get_current();
	}

	/**
	 * Get the current context.
	 *
	 * @return array
	 */
	public function get_current() {
		return end( $this->stack );
	}

	/**
	 * Reset context to global main query.
	 */
	public function reset_context() {
		$this->stack = array();

		// Default to current global post if available, else 0.
		$global_id = get_the_ID();
		
		$this->stack[] = array(
			'id'   => $global_id ? $global_id : 0,
			'type' => 'post',
		);
	}
}

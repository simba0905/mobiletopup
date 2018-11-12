<?php
/**
 * Register a meta box using a class.
 */
class movbileTopupAddMetaBoxProducts {

		private $meta_fields = array(

		array(
			'label' => 'Activate ThailandTopup',
			'id' => 'active_mobiletop_plugin',
			'type' => 'checkbox',
		),

		array(
			'label' => '10 THB',
			'id' => 'mobiletopup_amount_10',
			'type' => 'checkbox',
		),
		array(
			'label' => '20 THB',
			'id' => 'mobiletopup_amount_20',
			'type' => 'checkbox',
		),
		array(
			'label' => '30 THB',
			'id' => 'mobiletopup_amount_30',
			'type' => 'checkbox',
		),
		array(
			'label' => '50 THB',
			'id' => 'mobiletopup_amount_50',
			'type' => 'checkbox',
		),
		array(
			'label' => '100 THB',
			'id' => 'mobiletopup_amount_100',
			'type' => 'checkbox',
		),
		array(
			'label' => '200 THB',
			'id' => 'mobiletopup_amount_200',
			'type' => 'checkbox',
		),
		array(
			'label' => '300 THB',
			'id' => 'mobiletopup_amount_300',
			'type' => 'checkbox',
		),
		array(
			'label' => '500 THB',
			'id' => 'mobiletopup_amount_500',
			'type' => 'checkbox',
		),

		array(
			'label' => '800 THB',
			'id' => 'mobiletopup_amount_800',
			'type' => 'checkbox',
		),

		array(
			'label' => 'Validity Adding',
			'id' => 'mobiletopup_amount_repeat',
			'type' => 'radio',
			'options' => array(
				'Default',
				'90', // 10 * 30 90 days
				'180'  ,// 10 * 6 180 days
        '360', // 10 * 12, 360 days
			),
		),

    );
    /**
     * Constructor.
     */
    public function __construct() {

		if ( is_admin() ) {
            add_action( 'load-post.php',     array( $this, 'init_metabox' ) );
            add_action( 'load-post-new.php', array( $this, 'init_metabox' ) );
        }
    }

    /**
     * Meta box initialization.
     */
    public function init_metabox() {
        add_action( 'add_meta_boxes', array( $this, 'add_metabox'  )        );
        add_action( 'save_post',      array( $this, 'save_metabox' ), 10, 2 );
    }

    /**
     * Adds the meta box for ThailandTopup credit sending.
     */
    public function add_metabox() {
        add_meta_box(
            'active-mobiletopup',
            __( 'Manage Thai Topup', 'mobiletopup' ),
            array( $this, 'render_metabox' ),
            'product',
            'side',
            'default'
        );

    }

    /**
     * Renders the meta box.
     */
    public function render_metabox( $post ) {
        // Add nonce for security and authentication.
		wp_nonce_field( 'products_nonce_action', 'active_plugin' );
		echo $this->render_field($post);
	}

	/**
	 *  Render input
	 */

	private function debug($data)
	{
		echo "<pre>";
		var_dump($data);
		echo "</pre>";
	}
	private function render_field($post){
		$output = '';
		$input = '';
		foreach ($this->meta_fields as $meta_field )
		{

			$meta_value = get_post_meta( $post->ID, $meta_field['id'], true );

			if ( empty( $meta_value ) ) {
				$meta_value = $meta_field['default'];
			}

			switch ( $meta_field['type'] ) {
				case 'radio':
					$input .= '<fieldset>';
					$input .= '<label >' . $meta_field['label'] . '</label>';
					$input .= '<div >';
					$i = 0;

					foreach ( $meta_field['options'] as $key => $value ) {
						$meta_field_value = !is_numeric( $key ) ? $key : $value;
						if($i !== 0 ){
							$required = '';
						}
						else{
							$required = 'id="required_radiobox"';
						}
						$input .= sprintf(
							'<input  %s class="%s" name="%s" type="radio" value="%s"> %s %s',

							$meta_value === $meta_field_value ? 'checked ' : '',
							$meta_field['id'],
							$meta_field['id'],
							$meta_field_value,
							$value.' Day',
							$i < count( $meta_field['options'] ) - 1 ? '<br>' : ''
						);
						$i++;
					}
					$input .= '</div>';
					$input .= '</fieldset>';
					break;
				case 'checkbox':
					$input .= '<fieldset>';
					$input .= sprintf(
						'<input id="%s" name="%s" type="%s" value="1" %s>',
						$meta_field['id'],
						$meta_field['id'],
						$meta_field['type'],
						$meta_value == 1 ? 'checked' : ''

						// checked($meta_value, true, true)
					);
					$input .= '<label>' . $meta_field['label'] . '</label>';
					$input .= '</fieldset>';
					break;
				default:
					$input .= '<fieldset>';
					$input .= sprintf(
						'<input %s id="%s" name="%s" type="%s" value="%s">',
						$meta_field['type'] !== 'color' ? 'style="width: 100%"' : '',
						$meta_field['id'],
						$meta_field['id'],
						$meta_field['type'],
						$meta_value
					);
					$input .= '</fieldset>';
			}


			$output = $input;
		}
		// $this->debug($input);
		return $output;
	}

    /**
     * Handles saving the meta box.
     *
     * @param int     $post_id Post ID.
     * @param WP_Post $post    Post object.
     * @return null
     */
    public function save_metabox( $post_id, $post ) {
        // Add nonce for security and authentication.
        $nonce_name   = isset( $_POST['active_plugin'] ) ? $_POST['active_plugin'] : '';
        $nonce_action = 'products_nonce_action';

        // Check if nonce is set.
        if ( ! isset( $nonce_name ) ) {
            return;
        }

        // Check if nonce is valid.
        if ( ! wp_verify_nonce( $nonce_name, $nonce_action ) ) {
            return;
        }

        // Check if user has permissions to save data.
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        // Check if not an autosave.
        if ( wp_is_post_autosave( $post_id ) ) {
            return;
        }

        // Check if not a revision.
        if ( wp_is_post_revision( $post_id ) ) {
            return;
		}

		// var_dump($_POST['active_mobiletop_plugin']); exit();

		foreach ( $this->meta_fields as $meta_field ) {

			if ( isset( $_POST[ $meta_field['id'] ] ) ) {

				switch ( $meta_field['type'] ) {
					case 'text':
						$_POST[ $meta_field['id'] ] = sanitize_text_field( $_POST[ $meta_field['id'] ] );
						break;
				}
				// var_dump($_POST[ $meta_field['id'] ]);
				// exit();
				update_post_meta( $post_id, $meta_field['id'], $_POST[ $meta_field['id'] ] );
			}
			else if ( $meta_field['type'] === 'checkbox' ) {
				if ($_POST[ $meta_field['id']] == NULL)
				{
					update_post_meta( $post_id, $meta_field['id'], '0' );
				}
				else{
					update_post_meta( $post_id, $meta_field['id'], $_POST[ $meta_field['id']] );
				}

			}
		}
    }
}

if (class_exists('movbileTopupAddMetaBoxProducts')) {
	new movbileTopupAddMetaBoxProducts;
};

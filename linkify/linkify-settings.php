<?php
class linkify_settings {
	private $options;
	protected $defaults;

	public function __construct() {
		$this->defaults = array(
			'load-server-side' => linkify::SERVER,
			'shortcode-only' => false,
			'rel-nofollow' => 'never',
			'target-blank' => 'external',
			'max-description' => 250,
			'text-colour' => '',
			'text-colour-hover' => '',
			'background' => '#F7F5E7',
			'border' => '#EAEAEA',
			'border-radius' => 0
		);
		if(is_admin()) {
			add_action('admin_menu', array($this, 'add_plugin_page'));
			add_action('admin_init', array($this, 'page_init'));
			if($_POST['linkify-reset'] === "Restore Defaults") {
				$this->restore_defaults();
			}
		}
	}

	public function get() { return get_option('linkify_options', $this->defaults); }

	public function restore_defaults() {
		update_option(
			'linkify_options', $this->defaults
		);
	}

	public function add_plugin_page() {
		add_options_page(
			'Settings Admin',
			'Linkify',
			'manage_options',
			'linkify-setting-admin',
			array($this, 'create_admin_page')
		);
	}

	public function create_admin_page() {
		$this->options = $this->get();//get_option('linkify_options');
		?>
		<div class="wrap">
			<?php screen_icon(); ?>
			<h2>Linkify Settings</h2>
			<form method="post" action="options.php" style='display:inline'>
			<?php
				settings_fields('linkify_option_group');
				do_settings_sections('linkify-setting-admin');
				submit_button('Save Changes', 'primary', '', false, 'style="display:inline"');
			?>
			</form>
			<form method='post' style='display:inline' action='<?=$_SERVER["REQUEST_URI"];?>'>
				<input type='hidden' name='linkify-reset' value='Restore Defaults' />
				<?php submit_button('Restore Defaults', 'secondary', '', false, 'style="display:inline" tabindex="32767"'); ?>
			</form>
			<hr />
			<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
<input type="hidden" name="cmd" value="_s-xclick">
<input type="hidden" name="encrypted" value="-----BEGIN PKCS7-----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-----END PKCS7-----
">
<input type="image" src="https://www.paypalobjects.com/en_US/GB/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal – The safer, easier way to pay online.">
<img alt="" border="0" src="https://www.paypalobjects.com/en_GB/i/scr/pixel.gif" width="1" height="1">
</form>

		</div>
		<?php
	}

	public function page_init() {
		register_setting(
			'linkify_option_group',
			'linkify_options'
		);

		add_settings_section(
			'linkify_functionality',
			'Functionality Settings',
			array($this, 'print_functionality_info'),
			'linkify-setting-admin'
		);

		add_settings_field(
			'load-server-side',
			'<a title="Better for SEO purposes and requires less processing overall, but takes up server space">Store thumbnails on server</a>',
			array($this, 'checkbox_field'),
			'linkify-setting-admin',
			'linkify_functionality',
			array(
				'name' => 'load-server-side',
				'opt-group' => 'linkify_options',
				'value' => linkify::SERVER,
				'default' => linkify::SERVER
			)
		);

		add_settings_field(
			'shortcode-only',
			'<a title="Prevents auto-linkify on single line URLs">Use only shortcodes</a>',
			array($this, 'checkbox_field'),
			'linkify-setting-admin',
			'linkify_functionality',
			array(
				'name' => 'shortcode-only',
				'opt-group' => 'linkify_options',
				'value' => true,
				'default' => true
			)
		);

		add_settings_field(
			'rel-nofollow',
			'<a title="Prevents sites from gaining search engine influence from your links">Add rel=\'nofollow\' to links</a>',
			array($this, 'radio_field'),
			'linkify-setting-admin',
			'linkify_functionality',
			array(
				'name' => 'rel-nofollow',
				'opt-group' => 'linkify_options',
				'options' => array(
					array( 'label' => 'All links', 'value' => 'all' ),
					array( 'label' => 'External links only', 'value' => 'external' ),
					array( 'label' => 'Never', 'value' => 'never' ),
				)
			)
		);

		add_settings_field(
			'target-blank',
			'<a title="Opens links in a new tab">Add target=\'_blank\' to links</a>',
			array($this, 'radio_field'),
			'linkify-setting-admin',
			'linkify_functionality',
			array(
				'name' => 'target-blank',
				'opt-group' => 'linkify_options',
				'options' => array(
					array( 'label' => 'All links', 'value' => 'all' ),
					array( 'label' => 'External links only', 'value' => 'external' ),
					array( 'label' => 'Never', 'value' => 'never' ),
				)
			)
		);

		add_settings_field(
			'max-description',
			'Maximum description length',
			array($this, 'standard_field'),
			'linkify-setting-admin',
			'linkify_functionality',
			array(
				'name' => 'max-description',
				'opt-group' => 'linkify_options',
				'type' => 'number'
			)
		);

		add_settings_section(
			'linkify_style',
			'Style Settings',
			array($this, 'print_colour_info'),
			'linkify-setting-admin'
		);

		add_settings_field(
			'style-theme',
			'Theme',
			array($this, 'theme_select'),
			'linkify-setting-admin',
			'linkify_style'
		);

		$style_settings = array(
			array('text-colour', 'Text Colour', 'color'),
			array('text-colour-hover', 'Hover Text Colour', 'color'),
			array('background', 'Background', 'color'),
			array('border', 'Border', 'color'),
			array('border-radius', 'Border Radius', 'number')
		);

		foreach($style_settings as $style) {
			$minicolors = $style[2] == 'color' ? "minicolors" : "";
			add_settings_field($style[0], $style[1], array($this, 'standard_field'), 'linkify-setting-admin', 'linkify_style', array(
				'type' => $style[2], 'opt-group' => 'linkify_options', 'name' => $style[0], 'additional' =>
					"class='linkify-style $minicolors'"
			));
		}
	}

	public function print_functionality_info() {
		print 'Enter your functionality settings:';
	}

	public function print_colour_info() {
		print 'Enter your colour settings (leave blank for theme default):';
	}

	public function radio_field($args) {
		foreach($args['options'] as $option) {
			$checked = $this->options[$args['name']] == $option['value'] ? "checked='checked'" : "";
			echo "<label><input type='radio' name='{$args['opt-group']}[{$args['name']}]' value='{$option['value']}' $checked /> {$option['label']}</label><br />";
		}
	}

	public function checkbox_field($args) {
		printf(
			'<input type="checkbox" id="%s" name="%s[%s]" value="%s" %s />',
			$args['name'],
			$args['opt-group'],
			$args['name'],
			esc_attr($args['value']),
			$this->options[$args['name']] == $args['default'] ? "checked='checked'" : ""
		);
	}

	public function standard_field($args) {
		$type = $args['type'];
		if($args['type'] == 'color') { $args['type'] = 'text'; }
		printf(
			'<input type="%s" id="%s" name="%s[%s]" value="%s" %s />',
			$args['type'],
			$args['name'],
			$args['opt-group'],
			$args['name'],
			esc_attr($this->options[$args['name']]),
			$args['additional']
		);
	}

	public function theme_select() {
	?>
		<select name='linkify_options[style-theme]' id='style-theme' style='width:135px'>
			<option>Custom</option>
			<option>Default</option>
			<option>Light</option>
			<option>Dark</option>
		</select>
		<script type='text/javascript'><!--
			jQuery('#style-theme').change(function() {
				switch(jQuery(this).val()) {
					case 'Default':
						jQuery('#text-colour').minicolors('value', '');
						jQuery('#text-colour-hover').minicolors('value', '');
						jQuery('#background').minicolors('value', '#F7F5E7');
						jQuery('#border').minicolors('value', '#EAEAEA');
						break;
					case 'Light':
						jQuery('#text-colour').minicolors('value', '#333333');
						jQuery('#text-colour-hover').minicolors('value', '#555555');
						jQuery('#background').minicolors('value', '#F7F5E7');
						jQuery('#border').minicolors('value', '#EAEAEA');
						break;
					case 'Dark':
						jQuery('#text-colour').minicolors('value', '#DEDEDE');
						jQuery('#text-colour-hover').minicolors('value', '#FFFFFF');
						jQuery('#background').minicolors('value', '#474747');
						jQuery('#border').minicolors('value', '#000000');
						break;
				}
			});
		--></script>
	<?php
	}
}

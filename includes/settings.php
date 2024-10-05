<?php
if ( ! defined( 'ABSPATH' ) ) { exit; } add_filter( 'gform_tooltips', array( 'GFIPPANELSMS_Pro_Settings', 'tooltips' ) ); class GFIPPANELSMS_Pro_Settings { protected static function check_access( $required_permission ) { if ( ! function_exists( 'wp_get_current_user' ) ) { include( ABSPATH . "wp-includes/pluggable.php" ); } return GFCommon::current_user_can_any( $required_permission ); } public static function tooltips( $tooltips ) { $tooltips["admin_default"] = __( "You can set several numbers. Separate with commas(,). for example : +16175551212,+16175551213. this numbers is default and you can change them later.", "GF_IPPanel" ); $tooltips["show_credit"] = __( "Activating this section is not recommended; since you must connect to the webservice provider every time you wish to check your credit’s status and this might cause the wordpress admin to reduce in speed.", "GF_IPPanel" ); $tooltips["country_code"] = __( "Your Mobile Country code. like : +1", "GF_IPPanel" ); $tooltips["gf_sms_sender"] = __( "Separate with commas (,)", "GF_IPPanel" ); $tooltips["show_adminbar"] = __( "Show Gravity forms SMS menu in adminbar?", "GF_IPPanel" ); $tooltips["sidebar_ajax"] = __( "Activate this option to replace merge tags value in SMS Sidebar via ajax (Entry details)", "GF_IPPanel" ); return $tooltips; } public static function settings() { wp_enqueue_script('GF_SMS_Chosen', GF_SMS_URL . '/assets/chosen_v1.8.5/chosen.jquery.min.js', array(), true); wp_enqueue_style('GF_SMS_Chosen', GF_SMS_URL . '/assets/chosen_v1.8.5/chosen.min.css'); $settings = GFIPPANELSMS_Pro::get_option(); $G_code = rgget( 'gateway' ) ? rgget( 'gateway' ) : ( ! empty( $settings["ws"] ) ? $settings["ws"] : '' ); $G_code = strtolower( $G_code ); $gateway_options = get_option( "gf_ippanelsms_" . $G_code ); if ( ! rgempty( "uninstall" ) ) { check_admin_referer( "uninstall", "gf_ippanelsms_uninstall" ); if ( ! self::check_access( "gravityforms_ippanelsms_uninstall" ) ) { die( __( "You don't have adequate permission to uninstall Gravity SMS Pro.", "GF_IPPanel" ) ); } else { GFIPPANELSMS_Pro_SQL::drop_table(); delete_option( "gf_sms_settings" ); delete_option( "gf_sms_version" ); delete_option( "gf_sms_installed" ); delete_option( "gf_sms_last_sender" ); foreach ( (array) GFIPPANELSMS_Pro_WebServices::get() as $code => $name ) { delete_option( "gf_ippanelsms_" . strtolower( $code ) ); } $plugin = GF_SMS_DIR . "/ippanel_sms_pro_gv.php"; update_option( 'recently_activated', array( $plugin => time() ) + (array) get_option( 'recently_activated' ) ); deactivate_plugins( $plugin ); ?>

                <div class="updated fade" style="padding:20px;">
					<?php
 echo sprintf( __( "Gravity Forms SMS Pro have been successfully uninstalled. It can be re-activated from the %splugins page%s.", "GF_IPPanel" ), "<a href='plugins.php'>", "</a>" ) ?>
                </div>

				<?php
 } return false; } else if ( ! rgempty( "gf_ippanelsms_submit" ) ) { check_admin_referer( "update", "gf_ippanelsms_update" ); $settings = array( "user_name" => rgpost( "gf_ippanelsms_user_name" ), "password" => rgpost( "gf_ippanelsms_password" ), "from" => rgpost( "gf_ippanelsms_from" ), "code" => rgpost( "gf_ippanelsms_code" ), "to" => rgpost( "gf_ippanelsms_to" ), "ws" => rgpost( "gf_ippanelsms_ws" ), "cr" => rgpost( "gf_ippanelsms_showcr" ), "menu" => rgpost( "gf_ippanelsms_menu" ), "sidebar_ajax" => rgpost( "gf_ippanelsms_sidebar_ajax" ) ); update_option( "gf_sms_settings", array_map( 'sanitize_text_field', $settings ) ); if ( rgpost( "gf_ippanelsms_ws" ) && rgpost( "gf_ippanelsms_ws" ) != 'no' ) { $Saved_Gateway = 'GFIPPANELSMS_Pro_' . strtoupper( sanitize_text_field( rgpost( "gf_ippanelsms_ws" ) ) ); if ( class_exists( $Saved_Gateway ) && method_exists( $Saved_Gateway, 'options' ) ) { $gateway_options = array(); foreach ( (array) $Saved_Gateway::options() as $option => $name ) { $gateway_options[ $option ] = sanitize_text_field( rgpost( "gf_ippanelsms_" . strtolower( sanitize_text_field( rgpost( "gf_ippanelsms_ws" ) ) ) . '_' . $option ) ); } update_option( "gf_ippanelsms_" . strtolower( sanitize_text_field( rgpost( "gf_ippanelsms_ws" ) ) ), $gateway_options ); } } if ( ! headers_sent() ) { wp_redirect( admin_url( 'admin.php?page=gf_settings&subview=gf_sms_pro&updated=true' ) ); exit; } } if ( rgget( 'updated' ) == 'true' ) { echo '<div class="updated fade" style="padding:6px">' . __( "Settings updated.", "GF_IPPanel" ) . '</div>'; } ?>

        <form method="post" action="">

			<?php wp_nonce_field( "update", "gf_ippanelsms_update" ) ?>

            <h3><span><i
                            class="fa fa fa-mobile"></i><?php echo '   ' . __( "Gravity SMS Pro settings", "GF_IPPanel" ) . '   '; ?></span>
            </h3>

			<?php
 if ( ! empty( $G_code ) && $G_code != 'no' ) { if ( $G_code == strtolower( $settings["ws"] ) && $credit = GFIPPANELSMS_Pro::credit( true ) ) { preg_match( '/([\d]+)/', $credit, $match ); $credit_int = isset( $match[0] ) ? $match[0] : $credit; $range = GFIPPANELSMS_Pro::range(); $max = isset( $range["max"] ) ? $range["max"] : 500; $min = isset( $range["min"] ) ? $range["min"] : 2; if ( intval( $credit_int ) >= $max ) { $color = '#008000'; } else if ( intval( $credit_int ) < $max && intval( $credit_int ) >= $min ) { $color = '#FFC600'; } else { $color = '#FF1454'; } ?>

                    <h5><?php _e( "Your SMS Credit : ", "GF_IPPanel" ) ?><span
                                style="color:<?php echo $color; ?> !important;"><?php echo $credit; ?></span></h5>

					<?php
 } } ?>

            <hr/>

            <table class="form-table">

                <tr>
                    <th scope="row"><label for="gf_ippanelsms_ws"><?php _e( "SMS Gateway", "GF_IPPanel" ); ?></label></th>

                    <td width="340">
                        <select id="gf_ippanelsms_ws" name="gf_ippanelsms_ws" style="width:100%;"
                                class="select-gateway<?php echo is_rtl() ? " chosen-rtl" : ""; ?>"
                                onchange="GF_SwitchGateway(jQuery(this).val());">

							<?php foreach ( (array) GFIPPANELSMS_Pro_WebServices::get() as $code => $name ) { ?>

                                <option style="padding:3px"
                                        value="<?php echo $code ?>" <?php echo esc_attr( $G_code ) == $code ? "selected='selected'" : "" ?>><?php echo $name ?></option>

							<?php } ?>

                        </select>
                    </td>

                    <td rowspan="2" valign="middle">
                    </td>
                </tr>


				<?php
 if ( ! empty( $G_code ) && $G_code != 'no' ) { $Gateway = 'GFIPPANELSMS_Pro_' . strtoupper( $G_code ); if ( class_exists( $Gateway ) && method_exists( $Gateway, 'options' ) ) { $flag = true; foreach ( (array) $Gateway::options() as $option => $name ) { ?>
                            <tr>
                                <th scope="row"><label
                                            for="gf_ippanelsms_<?php echo $G_code . '_' . $option; ?>"><?php echo $name; ?></label>
                                </th>
                                <td width="340">
                                    <input type="text" id="gf_ippanelsms_<?php echo $G_code . '_' . $option; ?>"
                                           name="gf_ippanelsms_<?php echo $G_code . '_' . $option; ?>"
                                           value="<?php echo esc_attr( $gateway_options[ $option ] ) ?>" size="50"
                                           style="padding: 5px; direction:ltr !important;text-align:left;"/>
                                </td>
                                <td rowspan="2" valign="middle">
                                </td>
                            </tr>
						<?php } } } ?>

                <tr>
                    <th scope="row">
                        <label for="gf_ippanelsms_from">
							<?php _e( "Sender (From)", "GF_IPPanel" ); ?>
							<?php gform_tooltip( 'gf_sms_sender' ) ?>
                        </label>

                    </th>
                    <td width="340">

                        <input type="text" id="gf_ippanelsms_from" name="gf_ippanelsms_from"
                               value="<?php echo esc_attr( $settings["from"] ) ?>" size="50"
                               style="padding: 5px; direction:ltr !important;text-align:left;"/><br/>
                    </td>
                </tr>


                <tr>
                    <th scope="row">
                        <label for="gf_ippanelsms_code">
							<?php _e( "Your Default Country Code", "GF_IPPanel" ); ?>
							<?php gform_tooltip( 'country_code' ) ?>
                        </label>
                    </th>
                    <td width="340">

                        <input type="text" id="gf_ippanelsms_code" name="gf_ippanelsms_code"
                               value="<?php echo esc_attr( $settings["code"] ) ?>" size="50"
                               style="padding: 5px; direction:ltr !important;text-align:left;"/><br/>

                    </td>
                </tr>


               <!-- <tr>
                    <th scope="row">
                        <label for="gf_ippanelsms_to">
							<?php ?>
							<?php ?>
                        </label>
                    </th>
                    <td width="340">

                        <input type="text" id="gf_ippanelsms_to" name="gf_ippanelsms_to"
                               value="<?php ?>" size="50"
                               style="padding: 5px; direction:ltr !important;text-align:left;"/><br/>

                    </td>
                </tr>-->

				<?php if ( ! empty( $flag ) && ! empty( $Gateway ) && $Gateway::credit() ) { ?>

                    <tr>
                        <th scope="row">
                            <label for="gf_ippanelsms_showcr">
								<?php _e( "Show Credit/Balance", "GF_IPPanel" ); ?>
								<?php gform_tooltip( 'show_credit' ) ?>
                            </label>
                        </th>
                        <td width="340">


                            <input type="radio" name="gf_ippanelsms_showcr" id="gf_ippanelsms_showcr_show"
                                   value="Show" <?php echo esc_attr( $settings["cr"] ) == "Show" ? "checked='checked'" : "" ?>/>
                            <label class="inline"
                                   for="gf_ippanelsms_showcr_show"><?php _e( "Yes", "GF_IPPanel" ); ?></label>&nbsp;&nbsp;&nbsp;

                            <input type="radio" name="gf_ippanelsms_showcr" id="gf_ippanelsms_showcr_no"
                                   value="No" <?php echo esc_attr( $settings["cr"] ) != "Show" ? "checked='checked'" : "" ?>/>
                            <label class="inline"
                                   for="gf_ippanelsms_showcr_no"><?php _e( "No ( Recommended )", "GF_IPPanel" ); ?></label>

                            <br/>

                        </td>
                    </tr>

					<?php
 } ?>

                <tr>
                    <th scope="row">
                        <label for="gf_ippanelsms_menu">
							<?php _e( "Admin Bar Menu", "GF_IPPanel" ); ?>
							<?php gform_tooltip( 'show_adminbar' ) ?>
                        </label>
                    </th>
                    <td width="340">


                        <input type="radio" name="gf_ippanelsms_menu" id="gf_ippanelsms_menu_show"
                               value="Show" <?php echo esc_attr( $settings["menu"] ) == "Show" ? "checked='checked'" : "" ?>/>
                        <label class="inline" for="gf_ippanelsms_menu_show"><?php _e( "Yes", "GF_IPPanel" ); ?></label>&nbsp;&nbsp;&nbsp;


                        <input type="radio" name="gf_ippanelsms_menu" id="gf_ippanelsms_menu_no"
                               value="No" <?php echo esc_attr( $settings["menu"] ) != "Show" ? "checked='checked'" : "" ?>/>
                        <label class="inline" for="gf_ippanelsms_menu_no"><?php _e( "No", "GF_IPPanel" ); ?></label>

                        <br/>

                    </td>
                </tr>


                <tr>
                    <th scope="row">
                        <label for="gf_ippanelsms_sidebar_ajax">
							<?php _e( "Replace merge tags value in SMS Sidebar", "GF_IPPanel" ); ?>
							<?php gform_tooltip( 'sidebar_ajax' ) ?>
                        </label>
                    </th>
                    <td width="340">


                        <input type="radio" name="gf_ippanelsms_sidebar_ajax" id="gf_ippanelsms_sidebar_ajax_Yes"
                               value="Yes" <?php echo empty( $settings["sidebar_ajax"] ) || esc_attr( $settings["sidebar_ajax"] ) != "No" ? "checked='checked'" : "" ?>/>
                        <label class="inline"
                               for="gf_ippanelsms_sidebar_ajax_Yes"><?php _e( "Yes", "GF_IPPanel" ); ?></label>&nbsp;&nbsp;&nbsp;

                        <input type="radio" name="gf_ippanelsms_sidebar_ajax" id="gf_ippanelsms_sidebar_ajax_no"
                               value="No" <?php echo ! empty( $settings["sidebar_ajax"] ) && esc_attr( $settings["sidebar_ajax"] ) == "No" ? "checked='checked'" : "" ?>/>
                        <label class="inline" for="gf_ippanelsms_sidebar_ajax_no"><?php _e( "No", "GF_IPPanel" ); ?></label>

                        <br/>

                    </td>
                </tr>


                <tr>
                    <th scope="row">
                        <input type="submit" name="gf_ippanelsms_submit" class="button-primary"
                               value="<?php _e( "Save Settings", "GF_IPPanel" ) ?>"/>
                    </th>
                </tr>

            </table>


        </form>
        <form action="" method="post">
			<?php wp_nonce_field( "uninstall", "gf_ippanelsms_uninstall" ) ?>
			<?php if ( self::check_access( "gravityforms_ippanelsms_uninstall" ) ) { ?>

                <div class="hr-divider"></div>
                <div class="delete-alert alert_red">
                    <h3><?php _e( "Uninstall Gravity Forms SMS Pro", "GF_IPPanel" ) ?></h3>
                    <div
                            class="gf_delete_notice"><?php _e( "<strong>Warning!</strong> This operation deletes ALL Gravity SMS Pro Informations.", "GF_IPPanel" ) ?></div>
                    <input type="submit" name="uninstall"
                           value="<?php _e( "Uninstall Gravity Forms SMS Pro", "GF_IPPanel" ) ?>" class="button"
                           onclick="return confirm('<?php _e( "Warning! ALL Gravity SMS Pro informations will be deleted. This cannot be undone. \'OK\' to delete, \'Cancel\' to stop", "GF_IPPanel" ) ?>'); "/>
                </div>
			<?php } ?>
        </form>


        <script type="text/javascript">
            function GF_SwitchGateway(code) {
                new_query = "gateway=" + code;
                document.location = document.location + "&" + new_query;
            }

            jQuery(document).ready(function () {
                jQuery(".select-gateway").chosen();
            });
        </script>
		<?php
 } } if ( defined( 'GF_SMS_GATEWAY' ) ) { $files = scandir( GF_SMS_GATEWAY ); if ( $files ) { foreach ( (array) $files as $file ) { $path_parts = pathinfo( GF_SMS_GATEWAY . $file ); if ( strpos( $file, '.php' ) ) { include 'gateways/' . $path_parts['filename'] . '.php'; $Gateway = 'GFIPPANELSMS_Pro_' . strtoupper( $path_parts['filename'] ); if ( class_exists( $Gateway ) ) { if ( method_exists( $Gateway, 'options' ) && method_exists( $Gateway, 'process' ) && method_exists( $Gateway, 'name' ) ) { add_filter( 'gf_sms_gateways', array( $Gateway, 'name' ) ); } } } } } }
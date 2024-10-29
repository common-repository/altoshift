<?php
    defined('ABSPATH') or die;

    global $chk;
    global $wp;

    if (isset($_POST['submit']) && isset($_GET['nonce']) &&
        wp_verify_nonce($_GET['nonce'], 'altoshift_save_layer')
        && current_user_can("manage_options")
    ) {
        altoshift_opt();
    }
    function altoshift_opt(){
        $als_layer_enabled = sanitize_text_field($_POST['als_layer_enabled']);
        $als_layer_script = stripslashes($_POST['als_layer_script']);
        
        global $chk;
        if( get_option('altoshift_layer_enabled') != trim($als_layer_enabled)){
            $chk = update_option( 'altoshift_layer_enabled', trim($als_layer_enabled));
        }
        // if( $als_layer_script != '' ){
            $chk = update_option( 'altoshift_layer_code', $als_layer_script);
        // }
        global $wp;
        wp_safe_redirect(add_query_arg( array(
            'page' => 'altoshift-layer',
        ), $wp->request ));
        // die();
    }

    $saveUrl = add_query_arg( array(
            'page' => 'altoshift-layer',
            'nonce' => wp_create_nonce('altoshift_save_layer')
    ), $wp->request );
?>
<div class="wrap">
  <h2>Layer Settings</h2>
  <div class="metabox-holder">
    <div class="postbox">
      <div style="margin-left:10px;margin-right:10px;">
        <form method="post" action="<?php echo $saveUrl; ?>">
          <table class="form-table">
            <tbody>
            <tr>
              <th scope="row">
                <label for="als_layer_enabled">Enable Layer</label>
              </th>
              <td>
                <input type="checkbox" name="als_layer_enabled" value="yes" <?php if (get_option('altoshift_layer_enabled') == 'yes'){echo 'checked';} ?>/>
              </td>
            </tr>
            <tr>
              <th scope="row">
                <label for="als_layer_script">Altoshift Layer</label>
              </th>
              <td>
                  <textarea name="als_layer_script" rows="15" cols="100"><?php echo get_option('altoshift_layer_code')?></textarea>
              </td>
            </tr>
            <tr>
              <th scope="row">&nbsp;</th>
              <td style="padding-top:10px;  padding-bottom:10px;">
                <input type="submit" name="submit" value="Save changes" class="button-primary" />
              </td>
            </tr>
            </tbody>
          </table>
        </form>
      </div>
    </div>
  </div>
</div>
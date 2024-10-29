<?php
    defined('ABSPATH') or die;
    global $chk;
    global $wp;

    if (isset($_POST['submit']) && isset($_GET['nonce']) &&
        wp_verify_nonce($_GET['nonce'], 'altoshift_save_feed')
        && current_user_can("manage_options")
    ) {
        altoshift_opt();
    }
    function altoshift_opt(){
        $alsPassword = sanitize_text_field($_POST['als-feed-password']);
        $alsPasswordProtected = sanitize_text_field($_POST['als-password-protected']);
        $alsFeedPage = sanitize_text_field($_POST['als-feed-page']);
        $alsFeedPost = sanitize_text_field($_POST['als-feed-post']);
        global $chk;
        if($alsPasswordProtected != 'yes'){
            $alsPasswordProtected = 'no';
        }
        if( get_option('altoshift_page_feed') != $alsFeedPage){
            $chk = update_option( 'altoshift_page_feed', $alsFeedPage);
        }
        if( get_option('altoshift_post_feed') != $alsFeedPost){
            $chk = update_option( 'altoshift_post_feed', $alsFeedPost);
        }
        if( get_option('altoshift_feed_password') != $alsPassword){
            $chk = update_option( 'altoshift_feed_password', $alsPassword);
        }
        if( get_option('als-password-protected') != $alsPasswordProtected){
            
            $chk = update_option( 'altoshift_feed_password_protected', $alsPasswordProtected);
        }
        global $wp;
        wp_safe_redirect(add_query_arg( array(
            'page' => 'altoshift-feed',
        ), $wp->request ));
    }
    $saveUrl = add_query_arg( array(
        'page' => 'altoshift-feed',
        'nonce' => wp_create_nonce('altoshift_save_feed')
    ), $wp->request );
?>
<div class="wrap">
  <div id="icon-options-general" class="icon32"> <br>
  </div>
  <h2>Data Feeds</h2>
  <div class="metabox-holder">
    <div class="postbox">
    <div style="margin-left:10px;margin-right:10px;">
        <form method="post" action="<?php echo $saveUrl; ?>">
          <table class="form-table">
            <tr>
              <?php include 'feed-url.php'; ?>
            </tr>
            <tr>
              <th scope="row"> Feed type </th>
              <td>
                <div style="margin-right:10px;">
                  <input type="checkbox" name="als-feed-page" value="yes" <?php if (get_option('altoshift_page_feed') == 'yes'){echo 'checked';} ?>/> Pages
                </div>
                <div style="margin-right:10px;margin-top:10px">
                  <input type="checkbox" name="als-feed-post" value="yes" <?php if (get_option('altoshift_post_feed') == 'yes'){echo 'checked';} ?>/> Post
                </div>
              </td>
            </tr>
            <tr>
              <th scope="row"> protected </th>
              <td>
                  <input type="checkbox" name="als-password-protected" value="yes" <?php if (get_option('altoshift_feed_password_protected') == 'yes'){echo 'checked';} ?>/>
              </td>
            </tr>
            <tr>
              <th scope="row">Password</th>
              <td>
                <input type="text" name="als-feed-password" value="<?php echo get_option('altoshift_feed_password');?>" style="width:350px;" />
              </td>
            </tr>
            <tr>
              <th scope="row">&nbsp;</th>
              <td style="padding-top:10px;  padding-bottom:10px;">
                <input type="submit" name="submit" value="Save changes" class="button-primary" />
              </td>
            </tr>
          </table>
        </form>
      </div>
    </div>
  </div>
</div>
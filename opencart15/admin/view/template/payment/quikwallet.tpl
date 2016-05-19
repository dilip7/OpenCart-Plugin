<?php echo $header; ?>
<div id="content">
   <div class="breadcrumb">
      <?php foreach ($breadcrumbs as $breadcrumb) { ?>
      <?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
      <?php } ?>
   </div>
   <?php if ($error_warning) { ?>
   <div class="warning"><?php echo $error_warning; ?></div>
   <?php } ?>
   <div class="box">
      <div class="heading">
         <h1><img src="view/image/payment.png" alt="" /> <?php echo $heading_title; ?></h1>
         <div class="buttons"><a onclick="$('#form').submit();" class="button"><?php echo $button_save; ?></a><a href="<?php echo $cancel; ?>" class="button"><?php echo $button_cancel; ?></a></div>
      </div>
      <div class="content">
         <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form">
            <table class="form">
               <tr>
                  <td><span class="required">*</span> <?php echo $entry_quikwallet_partnerid; ?></td>
                  <td><input type="text" name="quikwallet_partnerid" value="<?php echo $quikwallet_partnerid; ?>" />
                     <?php if ($error_quikwallet_partnerid) { ?>
                     <span class="error"><?php echo $error_quikwallet_partnerid; ?></span>
                     <?php } ?>
                  </td>
               </tr>
               <tr>
                  <td><span class="required">*</span> <?php echo $entry_quikwallet_secret; ?></td>
                  <td><input type="text" name="quikwallet_secret" value="<?php echo $quikwallet_secret; ?>" />
                     <?php if ($error_quikwallet_secret) { ?>
                     <span class="error"><?php echo $error_quikwallet_secret; ?></span>
                     <?php } ?>
                  </td>
               </tr>
               <tr>
                  <td><span class="required">*</span> <?php echo $entry_quikwallet_url; ?></td>
                  <td><input type="text" name="quikwallet_url" value="<?php echo $quikwallet_url; ?>" />
                     <?php if ($error_quikwallet_url) { ?>
                     <span class="error"><?php echo $error_quikwallet_url; ?></span>
                     <?php } ?>
                  </td>
               </tr>
               <tr>
                  <td><?php echo $entry_status; ?></td>
                  <td>
                     <select name="quikwallet_status">
                        <?php if ($quikwallet_status) { ?>
                        <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                        <option value="0"><?php echo $text_disabled; ?></option>
                        <?php } else { ?>
                        <option value="1"><?php echo $text_enabled; ?></option>
                        <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                        <?php } ?>
                     </select>
                  </td>
               </tr>
               <tr>
                  <td><?php echo $entry_sort_order; ?></td>
                  <td><input type="text" name="quikwallet_sort_order" value="<?php echo $quikwallet_sort_order; ?>" size="1" /></td>
               </tr>
            </table>
         </form>
      </div>
   </div>
</div>
<?php echo $footer; ?>

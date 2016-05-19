<?php echo $header; ?><?php echo $column_left; ?>
<div id="content">
  <div class="page-header">
    <div class="container-fluid">
      <div class="pull-right">
        <button type="submit" form="form-quikwallet" data-toggle="tooltip" title="<?php echo $button_save; ?>" class="btn btn-primary"><i class="fa fa-save"></i></button>
        <a href="<?php echo $cancel; ?>" data-toggle="tooltip" title="<?php echo $button_cancel; ?>" class="btn btn-default"><i class="fa fa-reply"></i></a></div>
      <h1><?php echo $heading_title; ?></h1>
      <ul class="breadcrumb">
        <?php foreach ($breadcrumbs as $breadcrumb) { ?>
        <li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
        <?php } ?>
      </ul>
    </div>
  </div>
  <div class="container-fluid">
    <?php if ($error_warning) { ?>
    <div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo $error_warning; ?>
      <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
    <?php } ?>
    <div class="panel panel-default">
      <div class="panel-heading">
        <h3 class="panel-title"><i class="fa fa-pencil"></i> <?php echo $text_edit; ?></h3>
      </div>
      <div class="panel-body">
        <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form-quikwallet" class="form-horizontal">
          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-qw-partnerid"><span data-toggle="tooltip" title="<?php echo $help_key_id; ?>"><?php echo $entry_quikwallet_partnerid; ?></span></label>
            <div class="col-sm-10">
              <input type="text" name="quikwallet_partnerid" value="<?php echo $quikwallet_partnerid; ?>" placeholder="<?php echo $entry_quikwallet_partnerid; ?>" id="input-qw-partnerid" class="form-control" />
              <?php if ($error_quikwallet_partnerid) { ?>
              <div class="text-danger"><?php echo $error_quikwallet_partnerid; ?></div>
              <?php } ?>
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-qw-secret"><?php echo $entry_quikwallet_secret; ?></label>
            <div class="col-sm-10">
              <input type="text" name="quikwallet_secret" value="<?php echo $quikwallet_secret; ?>" placeholder="<?php echo $entry_quikwallet_secret; ?>" id="input-qw-secret" class="form-control" />
              <?php if ($error_quikwallet_secret) { ?>
              <div class="text-danger"><?php echo $error_quikwallet_secret; ?></div>
              <?php } ?>
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-qw-url"><?php echo $entry_quikwallet_url; ?></label>
            <div class="col-sm-10">
              <input type="text" name="quikwallet_url" value="<?php echo $quikwallet_url; ?>" placeholder="<?php echo $entry_quikwallet_url; ?>" id="input-qw-url" class="form-control" />
              <?php if ($error_quikwallet_url) { ?>
              <div class="text-danger"><?php echo $error_quikwallet_url; ?></div>
              <?php } ?>
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-2 control-label" for="quikwallet-status"><?php echo $entry_status; ?></label>
            <div class="col-sm-10">
              <select name="quikwallet_status" id="quikwallet-status" class="form-control">
                <?php if ($quikwallet_status) { ?>
                <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                <option value="0"><?php echo $text_disabled; ?></option>
                <?php } else { ?>
                <option value="1"><?php echo $text_enabled; ?></option>
                <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                <?php } ?>
              </select>
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-sort-order"><?php echo $entry_sort_order; ?></label>
            <div class="col-sm-10">
              <input type="text" name="quikwallet_sort_order" value="<?php if($quikwallet_sort_order){echo $quikwallet_sort_order;} else echo 0; ?>" placeholder="<?php echo $entry_sort_order; ?>" id="input-sort-order" class="form-control" />
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<?php echo $footer; ?>

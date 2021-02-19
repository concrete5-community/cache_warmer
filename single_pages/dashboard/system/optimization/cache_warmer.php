<?php 
defined('C5_EXECUTE') or die('Access Denied.');

$page_types = array();
$all_page_types = \PageType::getList();
if ($all_page_types) {
    foreach ($all_page_types as $pt) {
        $page_types[$pt->getPageTypeID()] = $pt->getPageTypeName();
    }
}

$page_type_id = array();
if ($selected_page_types) {
    foreach ($selected_page_types as $pt) {
        $page_type_id[] = $pt->getPageTypeID();
    }
}
?>

<form method="post" action="<?php  echo $controller->action('save') ?>">
	<?php
    $token = \Core::make('token');
    $token->output('cache_warmer.settings'); ?>

    <div class="form-group">
        <?php echo $form->label('job_queue_batch', t('Job queue batch'))?>
        <div style="width: 100%">
            <?php
            echo $form->number('job_queue_batch',
                Config::get('cache_warmer.settings.job_queue_batch'),
                array('placeholder' => t('Defaults to %d', 5), 'min' => 1)
            );
            ?>
        </div>
    </div>

	<div class="form-group">
		<?php echo $form->label('max_pages', t('Limit number of pages that should be cached with Cache Warmer'))?>
		<div style="width: 100%">
			<?php
            echo $form->number('max_pages',
                Config::get('cache_warmer.settings.max_pages'),
                array('placeholder' => t('Defaults to %d', 200), 'min' => 1)
            );
            ?>
		</div>
	</div>
	
	<div class="form-group">
		<?php echo $form->label('page_type_id', t('Filter pages by page type')); ?>
		<div style="width: 100%">
			<?php
            echo $form->selectMultiple('page_type_id', $page_types, $page_type_id, array('style' => 'width: 100%')); ?>
		</div>
	</div>

	<?php echo t('To run Cache Warmer, go to <a href="%s">Automated Tasks</a>.', URL::to('/dashboard/system/optimization/jobs')); ?>

    <div class="alert alert-info" style="margin-top: 20px;">
        <?php
        echo t("If your web server times out, and the job 'keeps spinning', try to decrease the 'Job queue batch'.");
        ?>
        <br>
        <?php
        echo t("If your web site contains thousands of pages, consider filtering on page type to only 'cache warm' the most important pages.");
        ?>
    </div>

	<div class="ccm-dashboard-form-actions-wrapper">
		<div class="ccm-dashboard-form-actions">
			<button class="pull-right btn btn-primary"><?php echo t('Save') ?></button>
		</div>
	</div>
</form>

<script type="text/javascript">
	$(function() {
		$('#page_type_id').removeClass('form-control').select2();
	});
</script>

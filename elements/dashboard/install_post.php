<?php

defined('C5_EXECUTE') or die('Access Denied.');

use Concrete\Core\Support\Facade\Package;
use Concrete\Core\Support\Facade\Url;

/** @var \Concrete\Core\Entity\Package $package */
$package = Package::getByHandle('cache_warmer');
?>
<p><?php echo t('Congratulations, the add-on has been installed!'); ?></p>
<br>

<p>
    <strong><?php echo t('You can find the add-on under:'); ?></strong><br>
    <a class="btn btn-default" href="<?php echo Url::to('/dashboard/system/optimization/cache_warmer') ?>">
        <?php
        echo t('Dashboard / System & Settings / Optimization');
        ?>
    </a>
</p>

<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

$id      = empty($displayData['id']) ? '' : (' id="' . $displayData['id'] . '"');
$target  = empty($displayData['target']) ? '' : (' target="' . $displayData['target'] . '"');
$onclick = empty($displayData['onclick']) ? '' : (' onclick="' . $displayData['onclick'] . '"');

// Image above a number is small, a single image is big
$imgsize = '';
if (!empty($displayData['image']))
{ 
	$imgsize = isset($displayData['amount']) ? 'small' : 'big';
}

// The title for the link (a11y)
$title   = empty($displayData['title']) ? '' : (' title="' . $this->escape($displayData['title']) . '"');
$specialclass  = empty($displayData['class']) ? '' : ('  ' . $this->escape($displayData['class']) . '"');
$text    = empty($displayData['text']) ? '' : ('<span class="j-links-link">' . $displayData['text'] . '</span>');

// Additinal button
$text2    = empty($displayData['text2']) ? '' : ($displayData['text2']);
$title2  = empty($displayData['title2']) ? '' : (' title="' . $this->escape($displayData['title2']) . '"');
$add	  = empty($displayData['name2']) ? '' : $displayData['name2'];

// For update buttons via plugins
$class    = '';
if ($id !== '')
{
	$class = ($displayData['id'] === 'plg_quickicon_joomlaupdate'
		|| $displayData['id'] === 'plg_quickicon_extensionupdate'
		|| $displayData['id'] === 'plg_quickicon_privacycheck'
		|| $displayData['id'] === 'plg_quickicon_overridecheck') ? ' class="pulse"' : '';
}

?>

		<li class="col mb-3 d-flex <?php echo !empty($displayData['link2']) ? 'flex-column' : ''; ?><?php echo $specialclass; ?>">
		<?php // The big button with image, [amount], text ?>
		<a <?php echo $id . $class; ?> href="<?php echo $displayData['link']; ?>"<?php echo $target . $onclick . $title; ?>>

			<?php if (isset($displayData['image'])): ?>
				<div class="<?php echo $imgsize;?> d-flex align-items-end">
					<div class="<?php echo $displayData['image']; ?>" aria-hidden="true"></div>
				</div>
			<?php endif; ?>

			<?php if (isset($displayData['amount'])): ?>
				<div class="quickicon-amount">
					<?php if (isset($displayData['ajaxurl'])):?>
						<span class="fa fa-spinner quickicon-counter"  data-url="<?php echo $displayData['ajaxurl']; ?>"></span>
					<?php else: 
						$amount = (int) $displayData['amount'];
						if ($amount <  100000):
							echo $amount;
						else:
							echo floor($amount / 1000) . '<span class="thsd">' . $amount % 1000 . '</span>';
						endif;
					?>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<?php if (isset($displayData['text'])): ?>
					<div class="quickicon-text d-flex align-items-center"><?php echo $text; ?></div>
			<?php endif; ?>
		</a>
		<?php // The small button with image2, text 2 ?>
		<?php if (!empty($displayData['link2'])): ?>
			<a class="quickicon-linkadd j-links-link mt-3" href="<?php echo $displayData['link2']; ?>">
				<span class="btn btn-link fa fa-plus mr-2" aria-hidden="true"></span>
				<span><?php echo $displayData['text2']; ?></span>
			</a>
		<?php endif; ?>
	</li>

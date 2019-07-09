<?php
/**
 * JEvents Component for Joomla! 3.x
 *
 * @version     $Id: cpanel.php 3119 2011-12-20 14:34:33Z geraintedwards $
 * @package     JEvents
 * @copyright   Copyright (C)  2008-2019 GWE Systems Ltd
 * @license     GNU/GPLv2, see http://www.gnu.org/licenses/gpl-2.0.html
 * @link        http://www.jevents.net
 */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;

if (JText::_("JEV_TRANSLATION_CREDITS") != "xJEV_TRANSLATION_CREDITS" && Factory::getLanguage()->getTag() != "xen-GB")
{
	?>
    <div class="gsl-width-expand gsl-text-center">
        <strong><?php echo JText::_("JEV_TRANSLATION_CREDITS"); ?>:</strong>
        <i><?php echo JText::_("JEV_TRANSLATION_LANGUAGE"); ?></i> - <?php echo JText::_("JEV_TRANSLATION_AUTHOR"); ?>
    </div>
	<?php
}

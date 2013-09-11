<?php
/**
 * This file is part of the PrestaCMSCoreBundle
 *
 * (c) PrestaConcept <www.prestaconcept.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Presta\CMSCoreBundle\Event;

/**
 * Event configuration for page deletion
 *
 * @author Nicolas Bastien nbastien@prestaconcept.net
 */
class PageDeletionEvent extends PageOperationEvent
{
    const EVENT_NAME = 'presta_cms.page.deletion';
}

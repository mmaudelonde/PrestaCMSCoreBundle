<?php
/**
 * This file is part of the PrestaCMSCoreBundle
 *
 * (c) PrestaConcept <www.prestaconcept.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Presta\CMSCoreBundle;

use Doctrine\Bundle\PHPCRBundle\DependencyInjection\Compiler\DoctrinePhpcrMappingsPass;
use Presta\CMSCoreBundle\DependencyInjection\Compiler\PageTypeCompilerPass;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Presta\CMSCoreBundle\DependencyInjection\Compiler\BlockCompilerPass;

/**
 * References :
 *   bundles : http://symfony.com/doc/current/book/bundles.html
 *
 * @author Nicolas Bastien <nbastien@prestaconcept.net>
 */
class PrestaCMSCoreBundle extends Bundle
{
    /**
     * {@inheritDoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new BlockCompilerPass());
        $container->addCompilerPass(new PageTypeCompilerPass());

        if (class_exists('Doctrine\Bundle\PHPCRBundle\DependencyInjection\Compiler\DoctrinePhpcrMappingsPass')) {
            $container->addCompilerPass(
                DoctrinePhpcrMappingsPass::createXmlMappingDriver(
                    array(
                        realpath($this->getPath() . '/Resources/config/doctrine-model') => 'Presta\CMSCoreBundle\Model',
                        realpath($this->getPath() . '/Resources/config/doctrine-phpcr') => 'Presta\CMSCoreBundle\Doctrine\Phpcr',
                    ),
                    array('cmf_content.manager_name')
                )
            );
        }
    }
}

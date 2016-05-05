<?php

/**
 * Extension compiler pass
 * This file is part of SergSXM UI package
 *
 * @package    SergSXM UI
 * @author     SergSXM <sergsxm@embedded.by>
 * @copyright  2016 SergSXM
 */

namespace Sergsxm\UIBundle\Services;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

class ExtensionPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->has('sergsxm.ui')) {
            return;
        }
        
        $definition = $container->findDefinition('sergsxm.ui');
        $taggedServices = $container->findTaggedServiceIds('sergsxm.uiextension');
        
        foreach ($taggedServices as $id => $tags) {
            $definition->addMethodCall('addExtension', array(new Reference($id)));
        }
    }    
}

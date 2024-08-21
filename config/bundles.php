<?php

$all = ['all' => true];
$onlyTest = ['dev' => true, 'test' => true, 'test_restricted' => true, 'test_restricted_conditional' => true];

$bundles = [
    Symfony\Bundle\FrameworkBundle\FrameworkBundle::class => $all,
    Symfony\Bundle\SecurityBundle\SecurityBundle::class => $all,
    Symfony\Bundle\TwigBundle\TwigBundle::class => $all,
    Symfony\Bundle\MonologBundle\MonologBundle::class => $all,
    Doctrine\Bundle\MongoDBBundle\DoctrineMongoDBBundle::class => $all,
    JMS\SerializerBundle\JMSSerializerBundle::class => $all,
    Graviton\RqlParserBundle\GravitonRqlParserBundle::class => $all,
    League\FlysystemBundle\FlysystemBundle::class => $all,
    Graviton\AnalyticsBundle\GravitonAnalyticsBundle::class => $all,
    Graviton\CommonBundle\GravitonCommonBundle::class => $all,
    Sentry\SentryBundle\SentryBundle::class => $all,
    Symfony\Bundle\DebugBundle\DebugBundle::class => $onlyTest,
    Symfony\Bundle\WebProfilerBundle\WebProfilerBundle::class => $onlyTest,
];

if (class_exists('Graviton\TestServicesBundle\GravitonTestServicesBundle')) {
    $bundles['Graviton\TestServicesBundle\GravitonTestServicesBundle'] = $onlyTest;
}

$ourOwnBundles = [
    Graviton\CoreBundle\GravitonCoreBundle::class => $all,
    Graviton\DocumentBundle\GravitonDocumentBundle::class => $all,
    Graviton\RestBundle\GravitonRestBundle::class => $all,
    Graviton\GeneratorBundle\GravitonGeneratorBundle::class => $all,
    Graviton\SecurityBundle\GravitonSecurityBundle::class => $all,
    Graviton\FileBundle\GravitonFileBundle::class => $all,
    Graviton\MigrationBundle\GravitonMigrationBundle::class => $all,
];

// dynamic bundles!
$dynamicBundles = [];
if (class_exists('GravitonDyn\BundleBundle\GravitonDynBundleBundle')) {
    foreach (GravitonDyn\BundleBundle\GravitonDynBundleBundle::getBundles() as $bundle) {
        $dynamicBundles[$bundle] = $all;
    }
}

return array_merge(
    $dynamicBundles,
    $bundles,
    $ourOwnBundles
);

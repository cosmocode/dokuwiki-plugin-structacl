<?php

namespace dokuwiki\plugin\structacl\test;

use DokuWikiTest;

/**
 * Config test for the structacl plugin
 *
 * @group plugin_structacl
 * @group plugins
 */
class ConfigTest extends DokuWikiTest
{
    protected $pluginsEnabled = ['struct', 'structacl'];

    /**
     * Simple config test
     */
    public function testConfig(): void
    {
        $confValue = 'schema1.assigned_user
schema1.reviewer
schema1.User No. 2
schema2.reviewer';

        $expected = [
            'schema1' => [
                'assigned_user',
                'reviewer',
                'User No. 2'
            ],
            'schema2' => [
                'reviewer'
            ]
        ];

        /** @var \helper_plugin_structacl $helper */
        $helper = plugin_load('helper', 'structacl');

        $config = $helper->getConfiguration($confValue);

        $this->assertSame($expected, $config);
    }

    /**
     * Test empty config
     */
    public function testConfigEmpty(): void
    {
        $confValue = '';
        $expected = [];

        /** @var \helper_plugin_structacl $helper */
        $helper = plugin_load('helper', 'structacl');

        $config = $helper->getConfiguration($confValue);

        $this->assertSame($expected, $config);
    }

    /**
     * Test invalid config
     */
    public function testConfigInvalid(): void
    {
        $confValue = 'schema:field';
        $expected = [];

        /** @var \helper_plugin_structacl $helper */
        $helper = plugin_load('helper', 'structacl');

        $config = $helper->getConfiguration($confValue);

        $this->assertSame($expected, $config);
    }
}

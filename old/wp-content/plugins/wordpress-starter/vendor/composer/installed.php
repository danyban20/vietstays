<?php return array(
    'root' => array(
        'name' => 'siteground/wordpress-starter',
        'pretty_version' => 'dev-master',
        'version' => 'dev-master',
        'reference' => '60d748e5367430f6f6a04899c56d5d17258f5863',
        'type' => 'wordpress-plugin',
        'install_path' => __DIR__ . '/../../',
        'aliases' => array(),
        'dev' => true,
    ),
    'versions' => array(
        'charles-rumley/php-po-to-json' => array(
            'pretty_version' => 'dev-master',
            'version' => 'dev-master',
            'reference' => 'c2d228fbbb69b90b6c331141b89c4faf985e6152',
            'type' => 'package',
            'install_path' => __DIR__ . '/../charles-rumley/php-po-to-json',
            'aliases' => array(
                0 => '9999999-dev',
            ),
            'dev_requirement' => false,
        ),
        'composer/installers' => array(
            'pretty_version' => 'v1.12.0',
            'version' => '1.12.0.0',
            'reference' => 'd20a64ed3c94748397ff5973488761b22f6d3f19',
            'type' => 'composer-plugin',
            'install_path' => __DIR__ . '/./installers',
            'aliases' => array(),
            'dev_requirement' => false,
        ),
        'roundcube/plugin-installer' => array(
            'dev_requirement' => false,
            'replaced' => array(
                0 => '*',
            ),
        ),
        'sepia/po-parser' => array(
            'pretty_version' => '4.2.2',
            'version' => '4.2.2.0',
            'reference' => '8a6bbcd4a231b19e1b052c1c46b0fe2935331afe',
            'type' => 'library',
            'install_path' => __DIR__ . '/../sepia/po-parser',
            'aliases' => array(),
            'dev_requirement' => false,
        ),
        'shama/baton' => array(
            'dev_requirement' => false,
            'replaced' => array(
                0 => '*',
            ),
        ),
        'siteground/siteground-helper' => array(
            'pretty_version' => 'dev-master',
            'version' => 'dev-master',
            'reference' => '4296c1c29c5c1b27cd3e4050daa8d1e4d3a4c7a6',
            'type' => 'library',
            'install_path' => __DIR__ . '/../siteground/siteground-helper',
            'aliases' => array(
                0 => '9999999-dev',
            ),
            'dev_requirement' => false,
        ),
        'siteground/wordpress-starter' => array(
            'pretty_version' => 'dev-master',
            'version' => 'dev-master',
            'reference' => '60d748e5367430f6f6a04899c56d5d17258f5863',
            'type' => 'wordpress-plugin',
            'install_path' => __DIR__ . '/../../',
            'aliases' => array(),
            'dev_requirement' => false,
        ),
        'yahnis-elsts/plugin-update-checker' => array(
            'pretty_version' => 'v4.11',
            'version' => '4.11.0.0',
            'reference' => '3155f2d3f1ca5e7ed3f25b256f020e370515af43',
            'type' => 'library',
            'install_path' => __DIR__ . '/../yahnis-elsts/plugin-update-checker',
            'aliases' => array(),
            'dev_requirement' => false,
        ),
    ),
);

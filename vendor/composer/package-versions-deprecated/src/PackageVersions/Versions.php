<?php

declare(strict_types=1);

namespace PackageVersions;

use Composer\InstalledVersions;
use OutOfBoundsException;

class_exists(InstalledVersions::class);

/**
 * This class is generated by composer/package-versions-deprecated, specifically by
 * @see \PackageVersions\Installer
 *
 * This file is overwritten at every run of `composer install` or `composer update`.
 *
 * @deprecated in favor of the Composer\InstalledVersions class provided by Composer 2. Require composer-runtime-api:^2 to ensure it is present.
 */
final class Versions
{
    /**
     * @deprecated please use {@see self::rootPackageName()} instead.
     *             This constant will be removed in version 2.0.0.
     */
    const ROOT_PACKAGE_NAME = 'laravel/laravel';

    /**
     * Array of all available composer packages.
     * Dont read this array from your calling code, but use the \PackageVersions\Versions::getVersion() method instead.
     *
     * @var array<string, string>
     * @internal
     */
    const VERSIONS          = array (
  'anandsiddharth/laravel-paytm-wallet' => 'v1.0.16@5cf4c586e9b40cf01d8b9bc35e67f8d932318d4b',
  'apility/laravel-fcm' => 'v1.4.4@e1484d3831f346d5dc03d64c742e53b6f500f33c',
  'asm89/stack-cors' => 'v2.0.3@9cb795bf30988e8c96dd3c40623c48a877bc6714',
  'backpack/backupmanager' => 'v3.0.2@7e8dc4d32238befd7fe78d28c1ddb9e49779f17c',
  'backpack/crud' => '4.1.41@44770347b918e42c052d0a138c24535b43e32472',
  'backpack/filemanager' => '1.1.4@f3fbea46ee3bcb0dcd753d2de8362f955008e0f7',
  'backpack/logmanager' => 'v4.0.2@1ddc98de9f0f9aa9a3a0d338af547052e1aae522',
  'backpack/newscrud' => 'v4.0.4@b50e8811432eaec923bd64b74a84e3333c6572c6',
  'backpack/pagemanager' => '3.0.7@7599394b9c72a0e472a34365f6b7a3b54cebb532',
  'backpack/permissionmanager' => '6.0.5@f1ead608e2a9e2fce583e7a3761a7fa577a520ee',
  'backpack/revise-operation' => '1.0.6@6208330fda66499d0670aad88c7025c4e9840c8d',
  'backpack/settings' => '3.0.9@1efa57e3f4a23fee2eb8eec81da969e3cfc13034',
  'barryvdh/elfinder-flysystem-driver' => 'v0.3.0@5a6c893dfb97e9848d7b1e5e990e943af7bc3550',
  'barryvdh/laravel-debugbar' => 'v3.5.5@6420113d90bb746423fa70b9940e9e7c26ebc121',
  'barryvdh/laravel-elfinder' => 'v0.4.7@f2a5f7d2b69b7c2f5419b0b02874b91b6d480c6c',
  'brick/math' => '0.9.2@dff976c2f3487d42c1db75a3b180e2b9f0e72ce0',
  'cocur/slugify' => 'v4.0.0@3f1ffc300f164f23abe8b64ffb3f92d35cec8307',
  'composer/ca-bundle' => '1.2.9@78a0e288fdcebf92aa2318a8d3656168da6ac1a5',
  'composer/package-versions-deprecated' => '1.11.99.1@7413f0b55a051e89485c5cb9f765fe24bb02a7b6',
  'creativeorange/gravatar' => 'v1.0.20@8c2c1a3a59fdf05f50c9d9413dd9d2d50835e017',
  'cviebrock/eloquent-sluggable' => '7.0.2@b3616ff51a80f8f408879b2f41936e8b45748e13',
  'defuse/php-encryption' => 'v2.2.1@0f407c43b953d571421e0020ba92082ed5fb7620',
  'dnoegel/php-xdg-base-dir' => 'v0.1.1@8f8a6e48c5ecb0f991c2fdcf5f154a47d85f9ffd',
  'doctrine/cache' => '1.10.2@13e3381b25847283a91948d04640543941309727',
  'doctrine/dbal' => '2.13.0@67d56d3203b33db29834e6b2fcdbfdc50535d796',
  'doctrine/deprecations' => 'v0.5.3@9504165960a1f83cc1480e2be1dd0a0478561314',
  'doctrine/event-manager' => '1.1.1@41370af6a30faa9dc0368c4a6814d596e81aba7f',
  'doctrine/inflector' => '2.0.3@9cf661f4eb38f7c881cac67c75ea9b00bf97b210',
  'doctrine/lexer' => '1.2.1@e864bbf5904cb8f5bb334f99209b48018522f042',
  'dragonmantank/cron-expression' => 'v2.3.1@65b2d8ee1f10915efb3b55597da3404f096acba2',
  'egulias/email-validator' => '2.1.25@0dbf5d78455d4d6a41d186da50adc1122ec066f4',
  'ezyang/htmlpurifier' => 'v4.13.0@08e27c97e4c6ed02f37c5b2b20488046c8d90d75',
  'fideloper/proxy' => '4.4.1@c073b2bd04d1c90e04dc1b787662b558dd65ade0',
  'firebase/php-jwt' => 'v5.2.1@f42c9110abe98dd6cfe9053c49bc86acc70b2d23',
  'fruitcake/laravel-cors' => 'v2.0.3@01de0fe5f71c70d1930ee9a80385f9cc28e0f63a',
  'guzzlehttp/guzzle' => '6.5.5@9d4290de1cfd701f38099ef7e183b64b4b7b0c5e',
  'guzzlehttp/promises' => '1.4.1@8e7d04f1f6450fef59366c399cfad4b9383aa30d',
  'guzzlehttp/psr7' => '1.8.1@35ea11d335fd638b5882ff1725228b3d35496ab1',
  'hisorange/browser-detect' => '4.4.0@4f1fb99a8ecb5d28117f9a44874ff9a1fd7d1a61',
  'intervention/image' => '2.5.1@abbf18d5ab8367f96b3205ca3c89fb2fa598c69e',
  'jaybizzle/crawler-detect' => 'v1.2.105@719c1ed49224857800c3dc40838b6b761d046105',
  'jenssegers/agent' => 'v2.6.4@daa11c43729510b3700bc34d414664966b03bffe',
  'laminas/laminas-diactoros' => '2.5.0@4ff7400c1c12e404144992ef43c8b733fd9ad516',
  'laminas/laminas-zendframework-bridge' => '1.2.0@6cccbddfcfc742eb02158d6137ca5687d92cee32',
  'laravel/framework' => 'v7.30.4@9dd38140dc2924daa1a020a3d7a45f9ceff03df3',
  'laravel/passport' => 'v9.4.0@011bd500e8ae3d459b692467880a49ff1ecd60c0',
  'laravel/tinker' => 'v2.6.1@04ad32c1a3328081097a181875733fa51f402083',
  'lcobucci/clock' => '2.0.0@353d83fe2e6ae95745b16b3d911813df6a05bfb3',
  'lcobucci/jwt' => '4.0.3@ae4165a76848e070fdac691e773243d10cd06ce1',
  'league/commonmark' => '1.5.8@08fa59b8e4e34ea8a773d55139ae9ac0e0aecbaf',
  'league/event' => '2.2.0@d2cc124cf9a3fab2bb4ff963307f60361ce4d119',
  'league/flysystem' => '1.1.3@9be3b16c877d477357c015cec057548cf9b2a14a',
  'league/flysystem-cached-adapter' => '1.1.0@d1925efb2207ac4be3ad0c40b8277175f99ffaff',
  'league/mime-type-detection' => '1.7.0@3b9dff8aaf7323590c1d2e443db701eb1f9aa0d3',
  'league/oauth2-server' => '8.2.4@622eaa1f28eb4a2dea0cfc7e4f5280fac794e83c',
  'league/pipeline' => '1.0.0@aa14b0e3133121f8be39e9a3b6ddd011fc5bb9a8',
  'maatwebsite/excel' => '3.1.30@aa5d2e4d25c5c8218ea0a15103da95f5f8728953',
  'maennchen/zipstream-php' => '2.1.0@c4c5803cc1f93df3d2448478ef79394a5981cc58',
  'markbaker/complex' => '2.0.0@9999f1432fae467bc93c53f357105b4c31bb994c',
  'markbaker/matrix' => '2.1.2@361c0f545c3172ee26c3d596a0aa03f0cef65e6a',
  'matomo/device-detector' => '4.2.2@dc270e7645d286d6f01d516a6634aba8b31ad668',
  'maximebf/debugbar' => 'v1.16.5@6d51ee9e94cff14412783785e79a4e7ef97b9d62',
  'mobiledetect/mobiledetectlib' => '2.8.37@9841e3c46f5bd0739b53aed8ac677fa712943df7',
  'monolog/monolog' => '2.2.0@1cb1cde8e8dd0f70cc0fe51354a59acad9302084',
  'mustangostang/spyc' => '0.6.3@4627c838b16550b666d15aeae1e5289dd5b77da0',
  'myclabs/php-enum' => '1.8.0@46cf3d8498b095bd33727b13fd5707263af99421',
  'nesbot/carbon' => '2.46.0@2fd2c4a77d58a4e95234c8a61c5df1f157a91bf4',
  'nikic/php-parser' => 'v4.10.4@c6d052fc58cb876152f89f532b95a8d7907e7f0e',
  'nyholm/psr7' => '1.4.0@23ae1f00fbc6a886cbe3062ca682391b9cc7c37b',
  'opis/closure' => '3.6.1@943b5d70cc5ae7483f6aff6ff43d7e34592ca0f5',
  'paragonie/random_compat' => 'v9.99.100@996434e5492cb4c3edcb9168db6fbb1359ef965a',
  'php-http/message-factory' => 'v1.0.2@a478cb11f66a6ac48d8954216cfed9aa06a501a1',
  'phpoffice/phpspreadsheet' => '1.16.0@76d4323b85129d0c368149c831a07a3e258b2b50',
  'phpoption/phpoption' => '1.7.5@994ecccd8f3283ecf5ac33254543eb0ac946d525',
  'phpseclib/phpseclib' => '2.0.31@233a920cb38636a43b18d428f9a8db1f0a1a08f4',
  'pragmarx/datatables' => 'v1.4.12@142631346d01c074248b80c8bc55fdfea255e23e',
  'pragmarx/support' => 'v0.9.3@fb0c7891ac6ce172f2d7e790cb70ef30a090f03c',
  'pragmarx/tracker' => 'v4.0.1@1985a681abe457a9ad6e6a04393b6739a34f720d',
  'prologue/alerts' => '0.4.8@a6412e318c0171526bc8b25ef597f2cc61f5b800',
  'psr/cache' => '1.0.1@d11b50ad223250cf17b86e38383413f5a6764bf8',
  'psr/container' => '1.1.1@8622567409010282b7aeebe4bb841fe98b58dcaf',
  'psr/event-dispatcher' => '1.0.0@dbefd12671e8a14ec7f180cab83036ed26714bb0',
  'psr/http-client' => '1.0.1@2dfb5f6c5eff0e91e20e913f8c5452ed95b86621',
  'psr/http-factory' => '1.0.1@12ac7fcd07e5b077433f5f2bee95b3a771bf61be',
  'psr/http-message' => '1.0.1@f6561bf28d520154e4b0ec72be95418abe6d9363',
  'psr/log' => '1.1.3@0f73288fd15629204f9d42b7055f72dacbe811fc',
  'psr/simple-cache' => '1.0.1@408d5eafb83c57f6365a3ca330ff23aa4a5fa39b',
  'psy/psysh' => 'v0.10.7@a395af46999a12006213c0c8346c9445eb31640c',
  'ralouphie/getallheaders' => '3.0.3@120b605dfeb996808c31b6477290a714d356e822',
  'ramsey/collection' => '1.1.3@28a5c4ab2f5111db6a60b2b4ec84057e0f43b9c1',
  'ramsey/uuid' => '4.1.1@cd4032040a750077205918c86049aa0f43d22947',
  'snowplow/referer-parser' => '0.2.0@5ce872b60999c63039723959a45928ef1196f03d',
  'spatie/db-dumper' => '2.21.1@05e5955fb882008a8947c5a45146d86cfafa10d1',
  'spatie/laravel-backup' => '6.15.1@94be6b3bb5248727367a50161be90e6c422558b4',
  'spatie/laravel-permission' => '4.0.1@29c05324c170c0be108ccb86dd29f6a719c0a617',
  'spatie/temporary-directory' => '1.3.0@f517729b3793bca58f847c5fd383ec16f03ffec6',
  'studio-42/elfinder' => '2.1.57@087524b1d7a4d76cfd848dee2093cd8daf987f78',
  'swiftmailer/swiftmailer' => 'v6.2.7@15f7faf8508e04471f666633addacf54c0ab5933',
  'symfony/console' => 'v5.2.6@35f039df40a3b335ebf310f244cb242b3a83ac8d',
  'symfony/css-selector' => 'v5.2.4@f65f217b3314504a1ec99c2d6ef69016bb13490f',
  'symfony/debug' => 'v4.4.20@157bbec4fd773bae53c5483c50951a5530a2cc16',
  'symfony/deprecation-contracts' => 'v2.2.0@5fa56b4074d1ae755beb55617ddafe6f5d78f665',
  'symfony/error-handler' => 'v5.2.6@bdb7fb4188da7f4211e4b88350ba0dfdad002b03',
  'symfony/event-dispatcher' => 'v5.2.4@d08d6ec121a425897951900ab692b612a61d6240',
  'symfony/event-dispatcher-contracts' => 'v2.2.0@0ba7d54483095a198fa51781bc608d17e84dffa2',
  'symfony/finder' => 'v5.2.4@0d639a0943822626290d169965804f79400e6a04',
  'symfony/http-client-contracts' => 'v2.3.1@41db680a15018f9c1d4b23516059633ce280ca33',
  'symfony/http-foundation' => 'v5.2.4@54499baea7f7418bce7b5ec92770fd0799e8e9bf',
  'symfony/http-kernel' => 'v5.2.6@f34de4c61ca46df73857f7f36b9a3805bdd7e3b2',
  'symfony/mime' => 'v5.2.6@1b2092244374cbe48ae733673f2ca0818b37197b',
  'symfony/polyfill-ctype' => 'v1.22.1@c6c942b1ac76c82448322025e084cadc56048b4e',
  'symfony/polyfill-iconv' => 'v1.22.1@06fb361659649bcfd6a208a0f1fcaf4e827ad342',
  'symfony/polyfill-intl-grapheme' => 'v1.22.1@5601e09b69f26c1828b13b6bb87cb07cddba3170',
  'symfony/polyfill-intl-idn' => 'v1.22.1@2d63434d922daf7da8dd863e7907e67ee3031483',
  'symfony/polyfill-intl-normalizer' => 'v1.22.1@43a0283138253ed1d48d352ab6d0bdb3f809f248',
  'symfony/polyfill-mbstring' => 'v1.22.1@5232de97ee3b75b0360528dae24e73db49566ab1',
  'symfony/polyfill-php72' => 'v1.22.1@cc6e6f9b39fe8075b3dabfbaf5b5f645ae1340c9',
  'symfony/polyfill-php73' => 'v1.22.1@a678b42e92f86eca04b7fa4c0f6f19d097fb69e2',
  'symfony/polyfill-php80' => 'v1.22.1@dc3063ba22c2a1fd2f45ed856374d79114998f91',
  'symfony/process' => 'v5.2.4@313a38f09c77fbcdc1d223e57d368cea76a2fd2f',
  'symfony/psr-http-message-bridge' => 'v2.1.0@81db2d4ae86e9f0049828d9343a72b9523884e5d',
  'symfony/routing' => 'v5.2.6@31fba555f178afd04d54fd26953501b2c3f0c6e6',
  'symfony/service-contracts' => 'v2.2.0@d15da7ba4957ffb8f1747218be9e1a121fd298a1',
  'symfony/string' => 'v5.2.6@ad0bd91bce2054103f5eaa18ebeba8d3bc2a0572',
  'symfony/translation' => 'v5.2.6@2cc7f45d96db9adfcf89adf4401d9dfed509f4e1',
  'symfony/translation-contracts' => 'v2.3.0@e2eaa60b558f26a4b0354e1bbb25636efaaad105',
  'symfony/var-dumper' => 'v5.2.6@89412a68ea2e675b4e44f260a5666729f77f668e',
  'tijsverkoyen/css-to-inline-styles' => '2.2.3@b43b05cf43c1b6d849478965062b6ef73e223bb5',
  'ua-parser/uap-php' => 'v3.9.14@b796c5ea5df588e65aeb4e2c6cce3811dec4fed6',
  'venturecraft/revisionable' => '1.38.0@7dd938dd3b4abe0ff1bbd7aaf8245f3d66dce205',
  'vlucas/phpdotenv' => 'v4.2.0@da64796370fc4eb03cc277088f6fede9fde88482',
  'voku/portable-ascii' => '1.5.6@80953678b19901e5165c56752d087fc11526017c',
  'backpack/generators' => 'v3.1.8@313b012399e98ece69942860d58a67e598de3662',
  'doctrine/instantiator' => '1.4.0@d56bf6102915de5702778fe20f2de3b2fe570b5b',
  'facade/flare-client-php' => '1.5.0@9dd6f2b56486d939c4467b3f35475d44af57cf17',
  'facade/ignition' => '2.7.0@bdc8b0b32c888f6edc838ca641358322b3d9506d',
  'facade/ignition-contracts' => '1.0.2@3c921a1cdba35b68a7f0ccffc6dffc1995b18267',
  'fakerphp/faker' => 'v1.14.1@ed22aee8d17c7b396f74a58b1e7fefa4f90d5ef1',
  'filp/whoops' => '2.12.0@d501fd2658d55491a2295ff600ae5978eaad7403',
  'hamcrest/hamcrest-php' => 'v2.0.1@8c3d0a3f6af734494ad8f6fbbee0ba92422859f3',
  'laracasts/generators' => '2.0.0@0b8b3d300cc948217f7547502b6de5db6fbafa70',
  'mockery/mockery' => '1.4.3@d1339f64479af1bee0e82a0413813fe5345a54ea',
  'myclabs/deep-copy' => '1.10.2@776f831124e9c62e1a2c601ecc52e776d8bb7220',
  'nunomaduro/collision' => 'v4.3.0@7c125dc2463f3e144ddc7e05e63077109508c94e',
  'phar-io/manifest' => '2.0.1@85265efd3af7ba3ca4b2a2c34dbfc5788dd29133',
  'phar-io/version' => '3.1.0@bae7c545bef187884426f042434e561ab1ddb182',
  'phpdocumentor/reflection-common' => '2.2.0@1d01c49d4ed62f25aa84a747ad35d5a16924662b',
  'phpdocumentor/reflection-docblock' => '5.2.2@069a785b2141f5bcf49f3e353548dc1cce6df556',
  'phpdocumentor/type-resolver' => '1.4.0@6a467b8989322d92aa1c8bf2bebcc6e5c2ba55c0',
  'phpspec/prophecy' => '1.13.0@be1996ed8adc35c3fd795488a653f4b518be70ea',
  'phpunit/php-code-coverage' => '7.0.14@bb7c9a210c72e4709cdde67f8b7362f672f2225c',
  'phpunit/php-file-iterator' => '2.0.3@4b49fb70f067272b659ef0174ff9ca40fdaa6357',
  'phpunit/php-text-template' => '1.2.1@31f8b717e51d9a2afca6c9f046f5d69fc27c8686',
  'phpunit/php-timer' => '2.1.3@2454ae1765516d20c4ffe103d85a58a9a3bd5662',
  'phpunit/php-token-stream' => '4.0.4@a853a0e183b9db7eed023d7933a858fa1c8d25a3',
  'phpunit/phpunit' => '8.5.15@038d4196d8e8cb405cd5e82cedfe413ad6eef9ef',
  'sebastian/code-unit-reverse-lookup' => '1.0.2@1de8cd5c010cb153fcd68b8d0f64606f523f7619',
  'sebastian/comparator' => '3.0.3@1071dfcef776a57013124ff35e1fc41ccd294758',
  'sebastian/diff' => '3.0.3@14f72dd46eaf2f2293cbe79c93cc0bc43161a211',
  'sebastian/environment' => '4.2.4@d47bbbad83711771f167c72d4e3f25f7fcc1f8b0',
  'sebastian/exporter' => '3.1.3@6b853149eab67d4da22291d36f5b0631c0fd856e',
  'sebastian/global-state' => '3.0.1@474fb9edb7ab891665d3bfc6317f42a0a150454b',
  'sebastian/object-enumerator' => '3.0.4@e67f6d32ebd0c749cf9d1dbd9f226c727043cdf2',
  'sebastian/object-reflector' => '1.1.2@9b8772b9cbd456ab45d4a598d2dd1a1bced6363d',
  'sebastian/recursion-context' => '3.0.1@367dcba38d6e1977be014dc4b22f47a484dac7fb',
  'sebastian/resource-operations' => '2.0.2@31d35ca87926450c44eae7e2611d45a7a65ea8b3',
  'sebastian/type' => '1.1.4@0150cfbc4495ed2df3872fb31b26781e4e077eb4',
  'sebastian/version' => '2.0.1@99732be0ddb3361e16ad77b68ba41efc8e979019',
  'theseer/tokenizer' => '1.2.0@75a63c33a8577608444246075ea0af0d052e452a',
  'webmozart/assert' => '1.10.0@6964c76c7804814a842473e0c8fd15bab0f18e25',
  'laravel/laravel' => 'dev-main@1a2b68625399e193a0b24c99b0f2b9ea2ee9a9f3',
);

    private function __construct()
    {
    }

    /**
     * @psalm-pure
     *
     * @psalm-suppress ImpureMethodCall we know that {@see InstalledVersions} interaction does not
     *                                  cause any side effects here.
     */
    public static function rootPackageName() : string
    {
        if (!class_exists(InstalledVersions::class, false) || !InstalledVersions::getRawData()) {
            return self::ROOT_PACKAGE_NAME;
        }

        return InstalledVersions::getRootPackage()['name'];
    }

    /**
     * @throws OutOfBoundsException If a version cannot be located.
     *
     * @psalm-param key-of<self::VERSIONS> $packageName
     * @psalm-pure
     *
     * @psalm-suppress ImpureMethodCall we know that {@see InstalledVersions} interaction does not
     *                                  cause any side effects here.
     */
    public static function getVersion(string $packageName): string
    {
        if (class_exists(InstalledVersions::class, false) && InstalledVersions::getRawData()) {
            return InstalledVersions::getPrettyVersion($packageName)
                . '@' . InstalledVersions::getReference($packageName);
        }

        if (isset(self::VERSIONS[$packageName])) {
            return self::VERSIONS[$packageName];
        }

        throw new OutOfBoundsException(
            'Required package "' . $packageName . '" is not installed: check your ./vendor/composer/installed.json and/or ./composer.lock files'
        );
    }
}

<?php
/**
 * As configurações básicas do WordPress
 *
 * O script de criação wp-config.php usa esse arquivo durante a instalação.
 * Você não precisa usar o site, você pode copiar este arquivo
 * para "wp-config.php" e preencher os valores.
 *
 * Este arquivo contém as seguintes configurações:
 *
 * * Configurações do banco de dados
 * * Chaves secretas
 * * Prefixo do banco de dados
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Configurações do banco de dados - Você pode pegar estas informações com o serviço de hospedagem ** //
/** O nome do banco de dados do WordPress */
define( 'DB_NAME', 'merry_pizzas' );

/** Usuário do banco de dados MySQL */
define( 'DB_USER', 'root' );

/** Senha do banco de dados MySQL */
define( 'DB_PASSWORD', '' );

/** Nome do host do MySQL */
define( 'DB_HOST', 'localhost' );

/** Charset do banco de dados a ser usado na criação das tabelas. */
define( 'DB_CHARSET', 'utf8mb4' );

/** O tipo de Collate do banco de dados. Não altere isso se tiver dúvidas. */
define( 'DB_COLLATE', '' );

/**#@+
 * Chaves únicas de autenticação e salts.
 *
 * Altere cada chave para um frase única!
 * Você pode gerá-las
 * usando o {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org
 * secret-key service}
 * Você pode alterá-las a qualquer momento para invalidar quaisquer
 * cookies existentes. Isto irá forçar todos os
 * usuários a fazerem login novamente.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'lwtkxYZ2nEn!uL};N@AMVJEz=`Umr{5mIpKg}C.}*uZ#&nPGxq!?oS#@uQN(%0x~' );
define( 'SECURE_AUTH_KEY',  ',9z;QP`dVWv[&=%Z!v>L&j,@XMfq&C,sblMe5wZ`:Y }C$*140zepe!$q{[iQk*l' );
define( 'LOGGED_IN_KEY',    '|)kneA !Vr?(kAeq-QnmpOekp;`-v%V-}o65){.iI;_dN[F+-uXY6(puLjCPP!Zu' );
define( 'NONCE_KEY',        '/hRE2,3+JK,`mc|=<*0~rE^q~bAa0oxs{vIpiCaoCNhQeigRsg]E>[<]a9uqu]XA' );
define( 'AUTH_SALT',        '^ifdKp~Rz=M^VJ,UA4>83pgx&-Bbd-/_5*rzB-_f)Q%hc&U%]PlUaKGMhKrN8bEg' );
define( 'SECURE_AUTH_SALT', '5bW/sa.7yKcYOdp%n[uoS8TB$.4`_D8hVRl}D/EtQw)RywNBgExj4;M?esQlblG)' );
define( 'LOGGED_IN_SALT',   'fw$xalzAoY_{iW%axn{>#c a,zw3nyezK-U`*dPjp=Ow:DeWLXihGhia>d^EJfJ6' );
define( 'NONCE_SALT',       'BS*<?$c!@CMmKmniEB<;h).U#Q>!h`1m.okDHyVs`5]sA?6dPJmc1qt*t)H2Eni@' );

/**#@-*/

/**
 * Prefixo da tabela do banco de dados do WordPress.
 *
 * Você pode ter várias instalações em um único banco de dados se você der
 * um prefixo único para cada um. Somente números, letras e sublinhados!
 */
$table_prefix = 'wp_';

/**
 * Para desenvolvedores: Modo de debug do WordPress.
 *
 * Altere isto para true para ativar a exibição de avisos
 * durante o desenvolvimento. É altamente recomendável que os
 * desenvolvedores de plugins e temas usem o WP_DEBUG
 * em seus ambientes de desenvolvimento.
 *
 * Para informações sobre outras constantes que podem ser utilizadas
 * para depuração, visite o Codex.
 *
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', false );

/* Adicione valores personalizados entre esta linha até "Isto é tudo". */



/* Isto é tudo, pode parar de editar! :) */

/** Caminho absoluto para o diretório WordPress. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Configura as variáveis e arquivos do WordPress. */
require_once ABSPATH . 'wp-settings.php';

<?php

// --- Redis: session storage & cache ---
$config['session_storage'] = 'redis';
$config['redis_hosts'] = ['redis:6379'];

$config['imap_cache'] = 'redis';
$config['messages_cache'] = true;
$config['messages_cache_threshold'] = 50;

// --- Cache TTLs ---
$config['imap_cache_ttl'] = '10d';
$config['messages_cache_ttl'] = '10d';

// --- Session ---
$config['session_lifetime'] = 30;

// --- User & UI ---
$config['auto_create_user'] = true;
$config['mail_pagesize'] = 50;
$config['draft_autosave'] = 60;
$config['preview_pane'] = true;
$config['check_all_folders'] = false;

// --- ManageSieve (Stalwart Mail Server) ---
$config['managesieve_port'] = 4190;
$config['managesieve_host'] = 'tls://%h';
$config['managesieve_auth_type'] = null;
$config['managesieve_usetls'] = false;
$config['managesieve_default'] = '/var/www/html/plugins/managesieve/defaut.sieve';
$config['managesieve_vacation'] = 1;
$config['managesieve_vacation_addresses_init'] = true;
$config['managesieve_raw_editor'] = true;

// --- Security ---
$config['ip_check'] = true;
$config['useragent'] = 'Roundcube Webmail';
$config['product_name'] = 'Webmail';

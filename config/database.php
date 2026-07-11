<?php

use Illuminate\Support\Str;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Database Connection Name
    |--------------------------------------------------------------------------
    |
    | Here you may specify which of the database connections below you wish
    | to use as your default connection for database operations. This is
    | the connection which will be utilized unless another connection
    | is explicitly specified when you execute a query / statement.
    |
    */

    'default' => env('DB_CONNECTION', 'sqlite'),

    /*
    |--------------------------------------------------------------------------
    | Database Connections
    |--------------------------------------------------------------------------
    |
    | Below are all of the database connections defined for your application.
    | An example configuration is provided for each database system which
    | is supported by Laravel. You're free to add / remove connections.
    |
    */

    'connections' => [

        'sqlite' => [
            'driver' => 'sqlite',
            'url' => env('DB_URL'),
            'database' => env('DB_DATABASE', database_path('database.sqlite')),
            'prefix' => '',
            'foreign_key_constraints' => env('DB_FOREIGN_KEYS', true),
            'busy_timeout' => null,
            'journal_mode' => null,
            'synchronous' => null,
        ],        

        'mysql' => [
            'driver' => 'mysql',
            'url' => env('DB_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'laravel'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => env('DB_CHARSET', 'utf8mb4'),
            'collation' => env('DB_COLLATION', 'utf8mb4_unicode_ci'),
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],

        /*
        'upload_image' => [
            'driver' => 'mysql',
            'url' => env('DB_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'test_file_upload'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => env('DB_CHARSET', 'utf8mb4'),
            'collation' => env('DB_COLLATION', 'utf8mb4_unicode_ci'),
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],*/

        'CES_DE_DABAYE' => [
            'driver' => 'mysql', 
            'host' => env('CES_DE_DABAYE_DB_HOST', 'localhost'), 
            'database' => env('CES_DE_DABAYE_DB_DATABASE', 'u732363477_CES_DE_DABAYE'),
            'username' => env('CES_DE_DABAYE_DB_USERNAME', 'u732363477_CES_DE_DABAYE'),
            'password' => env('CES_DE_DABAYE_DB_PASSWORD', 'SchoolMaster1_CES_DE_DABAYE'),
            //'username' => env('CES_DE_DABAYE_DB_USERNAME', 'root'),
            //'password' => env('CES_DE_DABAYE_DB_PASSWORD', ''), 
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],
        
        'CES_DE_LDIRI' => [
            'driver' => 'mysql', 
            'host' => env('CES_LDIRI_DB_HOST', 'localhost'), 
            'database' => env('CES_LDIRI_DB_DATABASE', 'u732363477_CES_LDIRI'),
            'username' => env('CES_LDIRI_DB_USERNAME', 'u732363477_CES_LDIRI'),
            'password' => env('CES_LDIRI_DB_PASSWORD', 'dms_acad1_CES_LDIRI'),  
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],
        
        'CES_DE_MOUROUM' => [
            'driver' => 'mysql', 
            'host' => env('CES_MOUROUM_DB_HOST', 'localhost'), 
            'database' => env('CES_MOUROUM_DB_DATABASE', 'u732363477_CES_MOUROUM'),
            'username' => env('CES_MOUROUM_DB_USERNAME', 'u732363477_CES_MOUROUM'),
            'password' => env('CES_MOUROUM_DB_PASSWORD', 'dms_acad1_CES_MOUROUM'),  
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],
        
        'CES_DE_SEDEK' => [
            'driver' => 'mysql', 
            'host' => env('CES_SEDEK_DB_HOST', 'localhost'), 
            'database' => env('CES_SEDEK_DB_DATABASE', 'u732363477_CES_SEDEK'),
            'username' => env('CES_SEDEK_DB_USERNAME', 'u732363477_CES_SEDEK'),
            'password' => env('CES_SEDEK_DB_PASSWORD', 'dms_acad1_CES_SEDEK'),  
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],
        
        'CES_DE_YOLDEO' => [
            'driver' => 'mysql', 
            'host' => env('CES_YOLDEO_DB_HOST', 'localhost'), 
            'database' => env('CES_YOLDEO_DB_DATABASE', 'u732363477_CES_YOLDEO'),
            'username' => env('CES_YOLDEO_DB_USERNAME', 'u732363477_CES_YOLDEO'),
            'password' => env('CES_YOLDEO_DB_PASSWORD', 'dms_acad1_CES_YOLDEO'),  
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],
        
        'CES_DE_ZIMADO' => [
            'driver' => 'mysql', 
            'host' => env('CES_ZIMADO_DB_HOST', 'localhost'), 
            'database' => env('CES_ZIMADO_DB_DATABASE', 'u732363477_CES_ZIMADO'),
            'username' => env('CES_ZIMADO_DB_USERNAME', 'u732363477_CES_ZIMADO'),
            'password' => env('CES_ZIMADO_DB_PASSWORD', 'dms_acad1_CES_ZIMADO'),  
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],
        
        
        'CETIC_DE_GADOUA' => [
            'driver' => 'mysql', 
            'host' => env('CETIC_GADOUA_DB_HOST', 'localhost'), 
            'database' => env('CETIC_GADOUA_DB_DATABASE', 'u732363477_CETIC_GADOUA'),
            'username' => env('CETIC_GADOUA_DB_USERNAME', 'u732363477_CETIC_GADOUA'),
            'password' => env('CETIC_GADOUA_DB_PASSWORD', 'dms_acad1_CETIC_GADOUA'),  
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],
        
        
        'CETIC_DE_BOGO' => [
            'driver' => 'mysql', 
            'host' => env('CETIC_BOGO_DB_HOST', 'localhost'), 
            'database' => env('CETIC_BOGO_DB_DATABASE', 'u732363477_CETIC_BOGO'),
            'username' => env('CETIC_BOGO_DB_USERNAME', 'u732363477_CETIC_BOGO'),
            'password' => env('CETIC_BOGO_DB_PASSWORD', 'dms_acad1_CETIC_BOGO'),  
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],
        
        'CETIC_DE_DARGALA' => [
            'driver' => 'mysql', 
            'host' => env('CETIC_DARGALA_DB_HOST', 'localhost'), 
            'database' => env('CETIC_DARGALA_DB_DATABASE', 'u732363477_CETIC_DARGALA'),
            'username' => env('CETIC_DARGALA_DB_USERNAME', 'u732363477_CETIC_DARGALA'),
            'password' => env('CETIC_DARGALA_DB_PASSWORD', 'dms_acad1_CETIC_DARGALA'),  
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ], 
        
        
        
                
        'CETIC_DE_DOUBANE' => [
            'driver' => 'mysql', 
            'host' => env('CETIC_DOUBANE_DB_HOST', 'localhost'), 
            'database' => env('CETIC_DOUBANE_DB_DATABASE', 'u732363477_CETIC_DOUBANE'),
            'username' => env('CETIC_DOUBANE_DB_USERNAME', 'u732363477_CETIC_DOUBANE'),
            'password' => env('CETIC_DOUBANE_DB_PASSWORD', 'dms_acad1_CETIC_DOUBANE'),  
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],
        
        
        'CETIC_DE_MAKARY' => [
            'driver' => 'mysql', 
            'host' => env('CETIC_MAKARY_DB_HOST', 'localhost'), 
            'database' => env('CETIC_MAKARY_DB_DATABASE', 'u732363477_CETIC_MAKARY'),
            'username' => env('CETIC_MAKARY_DB_USERNAME', 'u732363477_CETIC_MAKARY'),
            'password' => env('CETIC_MAKARY_DB_PASSWORD', 'dms_acad1_CETIC_MAKARY'),  
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],
        
        
        
        'COLLEGE_DE_LA_FRATERNITE' => [
            'driver' => 'mysql', 
            'host' => env('CO_FRATERNITE_DB_HOST', 'localhost'), 
            'database' => env('CO_FRATERNITE_DB_DATABASE', 'u732363477_FRATERNITE'),
            'username' => env('CO_FRATERNITE_DB_USERNAME', 'u732363477_FRATERNITE'),
            'password' => env('CO_FRATERNITE_DB_PASSWORD', 'dms_acad1_FRATERNITE'),  
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],
        
        
        
        'COLBIPPOLFOSH' => [
            'driver' => 'mysql', 
            'host' => env('COLBIPPOLFOSH_DB_HOST', 'localhost'), 
            'database' => env('COLBIPPOLFOSH_DB_DATABASE', 'u732363477_COLBIPPOLFOSH'),
            'username' => env('COLBIPPOLFOSH_DB_USERNAME', 'u732363477_COLBIPPOLFOSH'),
            'password' => env('COLBIPPOLFOSH_DB_PASSWORD', 'dms_acad1_COLBIPPOLFOSH'),  
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],
        
        
        
        'ENIEG_BILINGUE_DE_MAROUA' => [
            'driver' => 'mysql', 
            'host' => env('ENIEG_BI_DB_HOST', 'localhost'), 
            'database' => env('ENIEG_BI_DB_DATABASE', 'u732363477_ENIEG_BI'),
            'username' => env('ENIEG_BI_DB_USERNAME', 'u732363477_ENIEG_BI'),
            'password' => env('ENIEG_BI_DB_PASSWORD', 'dms_acad1_ENIEG_BI'),  
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],
        
        
        'ENIEG_DE_GUIDER' => [
            'driver' => 'mysql', 
            'host' => env('ENIEG_GUIDER_DB_HOST', 'localhost'), 
            'database' => env('ENIEG_GUIDER_DB_DATABASE', 'u732363477_ENIEG_GUIDER'),
            'username' => env('ENIEG_GUIDER_DB_USERNAME', 'u732363477_ENIEG_GUIDER'),
            'password' => env('ENIEG_GUIDER_DB_PASSWORD', 'dms_acad1_ENIEG_GUIDER'),  
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],
        
        
        'GBHS_MINAWAO' => [
            'driver' => 'mysql', 
            'host' => env('GHS_MINAWAO_DB_HOST', 'localhost'), 
            'database' => env('GHS_MINAWAO_DB_DATABASE', 'u732363477_GHS_MINAWAO'),
            'username' => env('GHS_MINAWAO_DB_USERNAME', 'u732363477_GHS_MINAWAO'),
            'password' => env('GHS_MINAWAO_DB_PASSWORD', 'dms_acad1_GHS_MINAWAO'),  
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],

        'GBTHS_MEWOULOU' => [
            'driver' => 'mysql', 
            'host' => env('GBTC_MEWOULOU_DB_HOST', 'localhost'), 
            'database' => env('GBTC_MEWOULOU_DB_DATABASE', 'u732363477_GBTC_MEWOULOU'),
            'username' => env('GBTC_MEWOULOU_DB_USERNAME', 'u732363477_GBTC_MEWOULOU'),
            'password' => env('GBTC_MEWOULOU_DB_PASSWORD', 'dms_acad1_GBTC_MEWOULOU'),  
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],

        
        'LB_BOGO' => [
            'driver' => 'mysql', 
            'host' => env('LB_BOGO_DB_HOST', 'localhost'), 
            'database' => env('LB_BOGO_DB_DATABASE', 'u732363477_LB_BOGO'),
            'username' => env('LB_BOGO_DB_USERNAME', 'u732363477_LB_BOGO'),
            'password' => env('LB_BOGO_DB_PASSWORD', 'dms_acad1_LB_BOGO'),  
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],
        
        
        'LB_KOZA' => [
            'driver' => 'mysql', 
            'host' => env('LB_KOZA_DB_HOST', 'localhost'), 
            'database' => env('LB_KOZA_DB_DATABASE', 'u732363477_LB_KOZA'),
            'username' => env('LB_KOZA_DB_USERNAME', 'u732363477_LB_KOZA'),
            'password' => env('LB_KOZA_DB_PASSWORD', 'dms_acad1_LB_KOZA'),  
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],
        
        
        'LB_MAKALINGAI' => [
            'driver' => 'mysql', 
            'host' => env('LB_MAKALINGAI_DB_HOST', 'localhost'), 
            'database' => env('LB_MAKALINGAI_DB_DATABASE', 'u732363477_LB_MAKALINGAI'),
            'username' => env('LB_MAKALINGAI_DB_USERNAME', 'u732363477_LB_MAKALINGAI'),
            'password' => env('LB_MAKALINGAI_DB_PASSWORD', 'dms_acad1_LB_MAKALINGAI'),  
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],
        
        
        'LB_ZAMAI' => [
            'driver' => 'mysql', 
            'host' => env('LB_ZAMAI_DB_HOST', 'localhost'), 
            'database' => env('LB_ZAMAI_DB_DATABASE', 'u732363477_LB_ZAMAI'),
            'username' => env('LB_ZAMAI_DB_USERNAME', 'u732363477_LB_ZAMAI'),
            'password' => env('LB_ZAMAI_DB_PASSWORD', 'dms_acad1_LB_ZAMAI'),  
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],
        
        
        'LT_DOUALARE' => [
            'driver' => 'mysql', 
            'host' => env('LT_DOUALARE_DB_HOST', 'localhost'), 
            'database' => env('LT_DOUALARE_DB_DATABASE', 'u732363477_LT_DOUALARE'),
            'username' => env('LT_DOUALARE_DB_USERNAME', 'u732363477_LT_DOUALARE'),
            'password' => env('LT_DOUALARE_DB_PASSWORD', 'dms_acad1_LT_DOUALARE'),  
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],
        
        
        'LT_GAZAWA' => [
            'driver' => 'mysql', 
            'host' => env('LT_GAZAWA_DB_HOST', 'localhost'), 
            'database' => env('LT_GAZAWA_DB_DATABASE', 'u732363477_LT_GAZAWA'),
            'username' => env('LT_GAZAWA_DB_USERNAME', 'u732363477_LT_GAZAWA'),
            'password' => env('LT_GAZAWA_DB_PASSWORD', 'dms_acad1_LT_GAZAWA'),  
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],
        
        
        'LT_KOZA' => [
            'driver' => 'mysql', 
            'host' => env('LT_KOZA_DB_HOST', 'localhost'), 
            'database' => env('LT_KOZA_DB_DATABASE', 'u732363477_LT_KOZA'),
            'username' => env('LT_KOZA_DB_USERNAME', 'u732363477_LT_KOZA'),
            'password' => env('LT_KOZA_DB_PASSWORD', 'dms_acad1_LT_KOZA'),  
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],
        
        
        'LT_LOGONE_BIRNI' => [
            'driver' => 'mysql', 
            'host' => env('LT_BIRNI_DB_HOST', 'localhost'), 
            'database' => env('LT_BIRNI_DB_DATABASE', 'u732363477_LT_BIRNI'),
            'username' => env('LT_BIRNI_DB_USERNAME', 'u732363477_LT_BIRNI'),
            'password' => env('LT_BIRNI_DB_PASSWORD', 'dms_acad1_LT_BIRNI'),  
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],
        
        

        'LT_MERI' => [
            'driver' => 'mysql', 
            'host' => env('LT_MERI_DB_HOST', 'localhost'), 
            'database' => env('LT_MERI_DB_DATABASE', 'u732363477_LT_MERI'),
            'username' => env('LT_MERI_DB_USERNAME', 'u732363477_LT_MERI'),
            'password' => env('LT_MERI_DB_PASSWORD', 'dms_acad1_LT_MERI'),  
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],
        
        
        'LT_MINDIF' => [
            'driver' => 'mysql', 
            'host' => env('LT_MINDIF_DB_HOST', 'localhost'), 
            'database' => env('LT_MINDIF_DB_DATABASE', 'u732363477_LT_MINDIF'),
            'username' => env('LT_MINDIF_DB_USERNAME', 'u732363477_LT_MINDIF'),
            'password' => env('LT_MINDIF_DB_PASSWORD', 'dms_acad1_LT_MINDIF'),  
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],
        
        
        'LT_MORA' => [
            'driver' => 'mysql', 
            'host' => env('LT_MORA_DB_HOST', 'localhost'), 
            'database' => env('LT_MORA_DB_DATABASE', 'u732363477_LT_MORA'),
            'username' => env('LT_MORA_DB_USERNAME', 'u732363477_LT_MORA'),
            'password' => env('LT_MORA_DB_PASSWORD', 'dms_acad1_LT_MORA'),  
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],
        
        
        
        'LYCEE_DE_BALAZA_ALCALI' => [
            'driver' => 'mysql', 
            'host' => env('LY_BALAZA_DB_HOST', 'localhost'), 
            'database' => env('LY_BALAZA_DB_DATABASE', 'u732363477_LY_BALAZA'),
            'username' => env('LY_BALAZA_DB_USERNAME', 'u732363477_LY_BALAZA'),
            'password' => env('LY_BALAZA_DB_PASSWORD', 'dms_acad1_LY_BALAZA'),  
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],
        
        
        'LYCEE_CLASSIQUE_DE_MAROUA' => [
            'driver' => 'mysql', 
            'host' => env('LC_MAROUA_DB_HOST', 'localhost'), 
            'database' => env('LC_MAROUA_DB_DATABASE', 'u732363477_LC_MAROUA'),
            'username' => env('LC_MAROUA_DB_USERNAME', 'u732363477_LC_MAROUA'),
            'password' => env('LC_MAROUA_DB_PASSWORD', 'dms_acad1_LC_MAROUA'),  
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],
        
        'LYCEE_DE_DOGBA' => [
            'driver' => 'mysql', 
            'host' => env('LY_DOGBA_DB_HOST', 'localhost'), 
            'database' => env('LY_DOGBA_DB_DATABASE', 'u732363477_LY_DOGBA'),
            'username' => env('LY_DOGBA_DB_USERNAME', 'u732363477_LY_DOGBA'),
            'password' => env('LY_DOGBA_DB_PASSWORD', 'dms_acad1_LY_DOGBA'),  
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],
        
        
        
        
        'LYCEE_DE_DOUALARE' => [
            'driver' => 'mysql', 
            'host' => env('LY_DOUALARE_DB_HOST', 'localhost'), 
            'database' => env('LY_DOUALARE_DB_DATABASE', 'u732363477_LY_DOUALARE'),
            'username' => env('LY_DOUALARE_DB_USERNAME', 'u732363477_LY_DOUALARE'),
            'password' => env('LY_DOUALARE_DB_PASSWORD', 'dms_acad1_LY_DOUALARE'),  
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],
        
        
        'LYCEE_DE_GABOUA' => [
            'driver' => 'mysql', 
            'host' => env('LY_GABOUA_DB_HOST', 'localhost'), 
            'database' => env('LY_GABOUA_DB_DATABASE', 'u732363477_LY_GABOUA'),
            'username' => env('LY_GABOUA_DB_USERNAME', 'u732363477_LY_GABOUA'),
            'password' => env('LY_GABOUA_DB_PASSWORD', 'dms_acad1_LY_GABOUA'),  
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],
        
        
        'LYCEE_DE_DOMO' => [
            'driver' => 'mysql', 
            'host' => env('LY_DOMO_DB_HOST', 'localhost'), 
            'database' => env('LY_DOMO_DB_DATABASE', 'u732363477_LY_DOMO'),
            'username' => env('LY_DOMO_DB_USERNAME', 'u732363477_LY_DOMO'),
            'password' => env('LY_DOMO_DB_PASSWORD', 'dms_acad1_LY_DOMO'),  
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],
        
        
        'LYCEE_DE_GODOLA' => [
            'driver' => 'mysql', 
            'host' => env('LY_GODOLA_DB_HOST', 'localhost'), 
            'database' => env('LY_GODOLA_DB_DATABASE', 'u732363477_LY_GODOLA'),
            'username' => env('LY_GODOLA_DB_USERNAME', 'u732363477_LY_GODOLA'),
            'password' => env('LY_GODOLA_DB_PASSWORD', 'dms_acad1_LY_GODOLA'),  
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],

        'LYCEE_DE_GUIDER' => [
            'driver' => 'mysql', 
            'host' => env('LY_GUIDER_DB_HOST', 'localhost'), 
            'database' => env('LY_GUIDER_DB_DATABASE', 'u732363477_LY_GUIDER'),
            'username' => env('LY_GUIDER_DB_USERNAME', 'u732363477_LY_GUIDER'),
            'password' => env('LY_GUIDER_DB_PASSWORD', 'dms_acad1_LY_GUIDER'),  
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],
        
        'LYCEE_DE_HARDE_MAROUA' => [
            'driver' => 'mysql', 
            'host' => env('LY_HARDE_DB_HOST', 'localhost'), 
            'database' => env('LY_HARDE_DB_DATABASE', 'u732363477_LY_HARDE'),
            'username' => env('LY_HARDE_DB_USERNAME', 'u732363477_LY_HARDE'),
            'password' => env('LY_HARDE_DB_PASSWORD', 'dms_acad1_LY_HARDE'),  
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],

        'LYCEE_DE_HOULA' => [
            'driver' => 'mysql', 
            'host' => env('LY_HOULA_DB_HOST', 'localhost'), 
            'database' => env('LY_HOULA_DB_DATABASE', 'u732363477_LY_HOULA'),
            'username' => env('LY_HOULA_DB_USERNAME', 'u732363477_LY_HOULA'),
            'password' => env('LY_HOULA_DB_PASSWORD', 'dms_acad1_LY_HOULA'),  
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],
        
        'LYCEE_DE_KAHEO' => [
            'driver' => 'mysql', 
            'host' => env('LY_KAHEO_DB_HOST', 'localhost'), 
            'database' => env('LY_KAHEO_DB_DATABASE', 'u732363477_LY_KAHEO'),
            'username' => env('LY_KAHEO_DB_USERNAME', 'u732363477_LY_KAHEO'),
            'password' => env('LY_KAHEO_DB_PASSWORD', 'dms_acad1_LY_KAHEO'),  
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],

        'LYCEE_DE_KALLIAO' => [
            'driver' => 'mysql', 
            'host' => env('LY_KALLIAO_DB_HOST', 'localhost'), 
            'database' => env('LY_KALLIAO_DB_DATABASE', 'u732363477_LY_KALLIAO'),
            'username' => env('LY_KALLIAO_DB_USERNAME', 'u732363477_LY_KALLIAO'),
            'password' => env('LY_KALLIAO_DB_PASSWORD', 'dms_acad1_LY_KALLIAO'),  
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],
        
        'LYCEE_DE_KOTRABA' => [
            'driver' => 'mysql', 
            'host' => env('LY_KOTRABA_DB_HOST', 'localhost'), 
            'database' => env('LY_KOTRABA_DB_DATABASE', 'u732363477_LY_KOTRABA'),
            'username' => env('LY_KOTRABA_DB_USERNAME', 'u732363477_LY_KOTRABA'),
            'password' => env('LY_KOTRABA_DB_PASSWORD', 'dms_acad1_LY_KOTRABA'),  
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],
       
       
        'LYCEE_DE_LOGONE_BIRNI' => [
            'driver' => 'mysql', 
            'host' => env('LY_BIRNI_DB_HOST', 'localhost'), 
            'database' => env('LY_BIRNI_DB_DATABASE', 'u732363477_LY_BIRNI'),
            'username' => env('LY_BIRNI_DB_USERNAME', 'u732363477_LY_BIRNI'),
            'password' => env('LY_BIRNI_DB_PASSWORD', 'dms_acad1_LY_BIRNI'),  
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],
        
        'LYCEE_DE_MAKABAYE' => [
            'driver' => 'mysql', 
            'host' => env('LY_MAKABAYE_DB_HOST', 'localhost'), 
            'database' => env('LY_MAKABAYE_DB_DATABASE', 'u732363477_LY_MAKABAYE'),
            'username' => env('LY_MAKABAYE_DB_USERNAME', 'u732363477_LY_MAKABAYE'),
            'password' => env('LY_MAKABAYE_DB_PASSWORD', 'dms_acad1_LY_MAKABAYE'),  
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],
        
        'LYCEE_DE_MAROUA_SALAK' => [
            'driver' => 'mysql', 
            'host' => env('LY_SALAK_DB_HOST', 'localhost'), 
            'database' => env('LY_SALAK_DB_DATABASE', 'u732363477_LY_SALAK'),
            'username' => env('LY_SALAK_DB_USERNAME', 'u732363477_LY_SALAK'),
            'password' => env('LY_SALAK_DB_PASSWORD', 'dms_acad1_LY_SALAK'),  
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],
        
        'LYCEE_DE_MASSAKAL' => [
            'driver' => 'mysql', 
            'host' => env('LY_MASSAKAL_DB_HOST', 'localhost'), 
            'database' => env('LY_MASSAKAL_DB_DATABASE', 'u732363477_LY_MASSAKAL'),
            'username' => env('LY_MASSAKAL_DB_USERNAME', 'u732363477_LY_MASSAKAL'),
            'password' => env('LY_MASSAKAL_DB_PASSWORD', 'dms_acad1_LY_MASSAKAL'),  
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],
        
 
        
        
        'LYCEE_DE_MERI' => [
            'driver' => 'mysql', 
            'host' => env('LY_MERI_DB_HOST', 'localhost'), 
            'database' => env('LY_MERI_DB_DATABASE', 'u732363477_LY_MERI'),
            'username' => env('LY_MERI_DB_USERNAME', 'u732363477_LY_MERI'),
            'password' => env('LY_MERI_DB_PASSWORD', 'dms_acad1_LY_MERI'),  
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],
        
        'LYCEE_DE_MOGOM' => [
            'driver' => 'mysql', 
            'host' => env('LY_MOGOM_DB_HOST', 'localhost'), 
            'database' => env('LY_MOGOM_DB_DATABASE', 'u732363477_LY_MOGOM'),
            'username' => env('LY_MOGOM_DB_USERNAME', 'u732363477_LY_MOGOM'),
            'password' => env('LY_MOGOM_DB_PASSWORD', 'dms_acad1_LY_MOGOM'),  
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],
        
        
        'LYCEE_DE_MOKIO' => [
            'driver' => 'mysql', 
            'host' => env('LY_MOKIO_DB_HOST', 'localhost'), 
            'database' => env('LY_MOKIO_DB_DATABASE', 'u732363477_LY_MOKIO'),
            'username' => env('LY_MOKIO_DB_USERNAME', 'u732363477_LY_MOKIO'),
            'password' => env('LY_MOKIO_DB_PASSWORD', 'dms_acad1_LY_MOKIO'),  
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],
        
        
        'LYCEE_DE_PITOA' => [
            'driver' => 'mysql', 
            'host' => env('LY_PITOA_DB_HOST', 'localhost'), 
            'database' => env('LY_PITOA_DB_DATABASE', 'u732363477_LY_PITOA'),
            'username' => env('LY_PITOA_DB_USERNAME', 'u732363477_LY_PITOA'),
            'password' => env('LY_PITOA_DB_PASSWORD', 'dms_acad1_LY_PITOA'),  
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],


        'LYCEE_DE_WAZA' => [
            'driver' => 'mysql', 
            'host' => env('LY_WAZA_DB_HOST', 'localhost'), 
            'database' => env('LY_WAZA_DB_DATABASE', 'u732363477_LY_WAZA'),
            'username' => env('LY_WAZA_DB_USERNAME', 'u732363477_LY_WAZA'),
            'password' => env('LY_WAZA_DB_PASSWORD', 'dms_acad1_LY_WAZA'),  
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],


        'TEST' => [
            'driver' => 'mysql', 
            'host' => env('TEST_DB_HOST', 'localhost'), 
            'database' => env('TEST_DB_DATABASE', 'u732363477_TEST'),
            'username' => env('TEST_DB_USERNAME', 'u732363477_TEST'),
            'password' => env('TEST_DB_PASSWORD', 'dms_acad1_TEST'),  
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],
        
        
        'TEST_PLAY' => [
            'driver' => 'mysql', 
            'host' => env('TEST_PLAY_DB_HOST', 'localhost'), 
            'database' => env('TEST_PLAY_DB_DATABASE', 'u732363477_TEST_PLAY'),
            'username' => env('TEST_PLAY_DB_USERNAME', 'u732363477_TEST_PLAY'),
            'password' => env('TEST_PLAY_DB_PASSWORD', 'dms_acad1_TEST_PLAY'),  
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],
        
        
        'Z_CLINIC' => [
            'driver' => 'mysql', 
            'host' => env('Z_CLINIC_DB_HOST', 'localhost'), 
            'database' => env('Z_CLINIC_DB_DATABASE', 'u732363477_Z_CLINIC'),
            'username' => env('Z_CLINIC_DB_USERNAME', 'u732363477_Z_CLINIC'),
            'password' => env('Z_CLINIC_DB_PASSWORD', 'dms_acad1_Z_CLINIC'),  
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],
        

        'mariadb' => [
            'driver' => 'mariadb',
            'url' => env('DB_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'laravel'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => env('DB_CHARSET', 'utf8mb4'),
            'collation' => env('DB_COLLATION', 'utf8mb4_unicode_ci'),
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],

        'pgsql' => [
            'driver' => 'pgsql',
            'url' => env('DB_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '5432'),
            'database' => env('DB_DATABASE', 'laravel'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => env('DB_CHARSET', 'utf8'),
            'prefix' => '',
            'prefix_indexes' => true,
            'search_path' => 'public',
            'sslmode' => 'prefer',
        ],

        'sqlsrv' => [
            'driver' => 'sqlsrv',
            'url' => env('DB_URL'),
            'host' => env('DB_HOST', 'localhost'),
            'port' => env('DB_PORT', '1433'),
            'database' => env('DB_DATABASE', 'laravel'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => env('DB_CHARSET', 'utf8'),
            'prefix' => '',
            'prefix_indexes' => true,
            // 'encrypt' => env('DB_ENCRYPT', 'yes'),
            // 'trust_server_certificate' => env('DB_TRUST_SERVER_CERTIFICATE', 'false'),
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Migration Repository Table
    |--------------------------------------------------------------------------
    |
    | This table keeps track of all the migrations that have already run for
    | your application. Using this information, we can determine which of
    | the migrations on disk haven't actually been run on the database.
    |
    */

    'migrations' => [
        'table' => 'migrations',
        'update_date_on_publish' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Redis Databases
    |--------------------------------------------------------------------------
    |
    | Redis is an open source, fast, and advanced key-value store that also
    | provides a richer body of commands than a typical key-value system
    | such as Memcached. You may define your connection settings here.
    |
    */

    'redis' => [

        'client' => env('REDIS_CLIENT', 'phpredis'),

        'options' => [
            'cluster' => env('REDIS_CLUSTER', 'redis'),
            'prefix' => env('REDIS_PREFIX', Str::slug(env('APP_NAME', 'laravel'), '_').'_database_'),
        ],

        'default' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_DB', '0'),
        ],

        'cache' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_CACHE_DB', '1'),
        ],

    ],

];

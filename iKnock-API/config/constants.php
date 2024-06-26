<?php

/*
    |--------------------------------------------------------------------------
    | Default Application Constants
    |--------------------------------------------------------------------------
    |
    */

return [

    'PAGINATION_PAGE_SIZE' => 100,
    'EXPORT_PAGE_SIZE' => 50000,
    'IS_EXPORT_IN_CHUNK' => false,
    'GOOGLE_API_KEY' => '',
    'STORAGE_UNIT_IMAGE_PATH' => '/uploads/storage_unit/',
    'GENERAL_IMAGE_PATH' => 'image/',
    'PRODUCT_IMAGE_PATH' => '/uploads/product/',
    'GALLERY_IMAGE_PATH' => '/uploads/gallery/',
    'USER_IMAGE_PATH' => '/uploads/user/',
    'USER_WISHLIST_LIMIT' => 3,
    'GOLD_MEMBER_AMOUNT' => '50',
    'MEDIA_IMAGE_PATH' => '/uploads/media/',
    'MEDIA_FILE_PATH' => 'app/uploads/user/',
    'APP_SALT' => 'this is salt string %$#as&*12.xzs! for abc',
    'UNAUTH_ROUTES' => [],
    'DIR_ADMIN' => 'admin',
    'BILLING' => [
        'PROBATION_DAY' => 23
    ],
    'LEAD_DEFAULT_COLUMNS' => ['lead_name', 'lead_type','auction','lead_value','original_loan','loan_date','sq_ft','yr_blt','eq','lead_status','address','city', 'county', 'state', 'zip_code', 'is_expired','created_by','updated_by','created_at','updated_at'],
    'SPECIAL_CHARACTERS' => [
        'IGNORE' =>['/', '|', '%'],
        'REPLACE' =>['_'],
    ],
//    'LEAD_IGNORE_COLUMNS' => ['lead_status','lead_type', 'is_expired'],
    // 'LEAD_IGNORE_COLUMNS' => ['is_expired'],
    'LEAD_IGNORE_COLUMNS' => ['lead_status','lead_type', 'is_expired','foreclosure_date', 'admin_notes'],
    'TEMPLATE_SHOW_LEAD_IGNORE_COLUMNS' => ['is_expired','foreclosure_date', 'admin_notes'],
    'LEAD_TITLE_DISPLAY' => 'Homeowner Name',
    'SUB_ADMIN_QUOTA' => 11,
    //title, lead_name','lead_type','lead_status', 'address', 'city', 'county', 'state', 'zip_code'


];

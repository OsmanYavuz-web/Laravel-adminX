<?php

return [
    'accepted' => ':attribute kabul edilmelidir.',
    'confirmed' => ':attribute onaylaması eşleşmiyor.',
    'email' => ':attribute geçerli bir e-posta adresi olmalıdır.',
    'required' => ':attribute alanı zorunludur.',
    'unique' => 'Bu :attribute daha önce kaydedilmiş.',
    'min' => [
        'numeric' => ':attribute en az :min olmalıdır.',
        'file' => ':attribute en az :min kilobayt olmalıdır.',
        'string' => ':attribute en az :min karakter olmalıdır.',
        'array' => ':attribute en az :min öge içermelidir.',
    ],
    'max' => [
        'numeric' => ':attribute en fazla :max olmalıdır.',
        'file' => ':attribute en fazla :max kilobayt olabilir.',
        'string' => ':attribute en fazla :max karakter olabilir.',
        'array' => ':attribute en fazla :max öge içerebilir.',
    ],
    'attributes' => [
        'name' => 'Ad Soyad',
        'email' => 'E-posta Adresi',
        'password' => 'Şifre',
        'locale' => 'Dil',
    ],
];

<?php
return [
    'name' => 'BELLABELL FARM',
    'wordmark' => ['BELLABELL', 'FARM'],
    'registration' => '340-86-02462',
    'commerce_registration' => '2023-제주노형-0049호',
    'email' => 'partner@jejubellabell.com',
    'source' => 'https://fromhalla.com/',
    'brands' => [
        ['id'=>'fromhalla','name'=>'FROM HALLA','url'=>'https://fromhalla.com/','media'=>'products','logo'=>'images/fromhalla-logo.svg','status'=>'published'],
        ['id'=>'aouaou','name'=>'AOUAOU','url'=>'https://www.visitjeju.net/kr/detail/view?contentsid=CNTS_300000000012656&menuId=DOM_200000000010940','media'=>'aouaou','logo'=>null,'status'=>'published'],
    ],
    'ingredients' => [
        ['id'=>'bamboo','media'=>'bamboo','status'=>'published'],
        ['id'=>'sea','media'=>'sea','status'=>'published'],
        ['id'=>'farm','media'=>'products','status'=>'published'],
    ],
    'research' => [
        ['id'=>'discover','media'=>'bamboo','status'=>'published'],
        ['id'=>'connect','media'=>'research','status'=>'published'],
        ['id'=>'develop','media'=>'products','status'=>'published'],
        ['id'=>'patents','media'=>'research','status'=>'draft','note'=>'Patent registration numbers and current certification evidence are not supplied.'],
    ],
    'business' => [
        ['id'=>'nutrition','media'=>'products','href'=>'brands#brand-fromhalla','status'=>'published'],
        ['id'=>'care','media'=>'bamboo','href'=>'technology','status'=>'published'],
        ['id'=>'experience','media'=>'aouaou','href'=>'brands#brand-aouaou','status'=>'published'],
    ],
    'history' => [
        ['year'=>'2022','id'=>'2022','source'=>'https://fromhalla.com/article/보도자료/2/305/','status'=>'published'],
        ['year'=>'2023','id'=>'2023','source'=>'https://ijto.or.kr/korean/Bd/view.php?bno=2253&btable=report_info&cate=0&p=3','status'=>'published'],
        ['year'=>'2025','id'=>'2025','source'=>'https://ijto.or.kr/korean/Bd/view.php?bno=2694&btable=report_info&lcate=&p=1','status'=>'published'],
    ],
    'news' => [
        ['id'=>'taipei','date'=>'2025.11','category'=>'global','url'=>'https://ijto.or.kr/korean/Bd/view.php?bno=2694&btable=report_info&lcate=&p=1','status'=>'published'],
        ['id'=>'jstartup','date'=>'2023.10','category'=>'company','url'=>'https://ijto.or.kr/korean/Bd/view.php?bno=2253&btable=report_info&cate=0&p=3','status'=>'published'],
        ['id'=>'paw','date'=>'2023.08','category'=>'impact','url'=>'https://fromhalla.com/article/보도자료/2/684/','status'=>'published'],
    ],
    'social' => [],
];

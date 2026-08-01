<?php

namespace Database\Seeders;

use App\Modules\ExcaCoin\Models\Dictionary;
use Illuminate\Database\Seeder;

class DictionarySeeder extends Seeder
{
    public function run(): void
    {
        // --- DÖNEMLER (PERIOD) ---
        $periods = [
            ['code' => 'ARC', 'tr' => 'Arkaik',              'en' => 'Archaic',              'sort' => 1],
            ['code' => 'CLA', 'tr' => 'Klasik',              'en' => 'Classical',             'sort' => 2],
            ['code' => 'HEL', 'tr' => 'Hellenistik',         'en' => 'Hellenistic',           'sort' => 3],
            ['code' => 'RREP', 'tr' => 'Roma Cumhuriyet',    'en' => 'Roman Republic',        'sort' => 4],
            ['code' => 'RIMP', 'tr' => 'Roma İmparatorluk',  'en' => 'Roman Imperial',        'sort' => 5],
            ['code' => 'RPROV', 'tr' => 'Roma Eyalet',       'en' => 'Roman Provincial',      'sort' => 6],
            ['code' => 'LROM', 'tr' => 'Geç Roma',           'en' => 'Late Roman',            'sort' => 7],
            ['code' => 'BYZ', 'tr' => 'Bizans',              'en' => 'Byzantine',             'sort' => 8],
        ];

        foreach ($periods as $p) {
            Dictionary::updateOrCreate(
                ['type' => 'period', 'name->tr' => $p['tr']],
                [
                    'code' => $p['code'],
                    'name' => ['tr' => $p['tr'], 'en' => $p['en']],
                    'sort_order' => $p['sort'],
                    'is_active' => true,
                ]
            );
        }

        // --- METALLER (METAL) ---
        $metals = [
            ['code' => 'AE', 'tr' => 'Bronz / Bakır', 'en' => 'Bronze / Copper', 'sort' => 1],
            ['code' => 'AR', 'tr' => 'Gümüş',          'en' => 'Silver',          'sort' => 2],
            ['code' => 'AU', 'tr' => 'Altın',           'en' => 'Gold',            'sort' => 3],
            ['code' => 'EL', 'tr' => 'Elektron',        'en' => 'Electrum',        'sort' => 4],
            ['code' => 'BI', 'tr' => 'Billon',          'en' => 'Billon',          'sort' => 5],
            ['code' => 'PB', 'tr' => 'Kurşun',          'en' => 'Lead',            'sort' => 6],
        ];

        foreach ($metals as $m) {
            Dictionary::updateOrCreate(
                ['type' => 'metal', 'code' => $m['code']],
                [
                    'name' => ['tr' => $m['tr'], 'en' => $m['en']],
                    'sort_order' => $m['sort'],
                    'is_active' => true,
                ]
            );
        }

        // --- BİRİMLER (DENOMINATION) ---
        $denominations = [
            ['code' => 'DRA', 'tr' => 'Drachm',        'en' => 'Drachm',        'sort' => 1],
            ['code' => 'DID', 'tr' => 'Didrachm',      'en' => 'Didrachm',      'sort' => 2],
            ['code' => 'TET', 'tr' => 'Tetradrachm',   'en' => 'Tetradrachm',   'sort' => 3],
            ['code' => 'OBO', 'tr' => 'Obol',          'en' => 'Obol',          'sort' => 4],
            ['code' => 'HOBO', 'tr' => 'Hemiobol',     'en' => 'Hemiobol',      'sort' => 5],
            ['code' => 'DEN', 'tr' => 'Denarius',      'en' => 'Denarius',      'sort' => 6],
            ['code' => 'AS',  'tr' => 'As',            'en' => 'As',            'sort' => 7],
            ['code' => 'DUP', 'tr' => 'Dupondius',     'en' => 'Dupondius',     'sort' => 8],
            ['code' => 'SES', 'tr' => 'Sestertius',    'en' => 'Sestertius',    'sort' => 9],
            ['code' => 'ANT', 'tr' => 'Antoninianus',  'en' => 'Antoninianus',  'sort' => 10],
            ['code' => 'FOL', 'tr' => 'Follis',        'en' => 'Follis',        'sort' => 11],
            ['code' => 'SOL', 'tr' => 'Solidus',       'en' => 'Solidus',       'sort' => 12],
            ['code' => 'NUM', 'tr' => 'Nummus',        'en' => 'Nummus',        'sort' => 13],
        ];

        foreach ($denominations as $d) {
            Dictionary::updateOrCreate(
                ['type' => 'denomination', 'name->tr' => $d['tr']],
                [
                    'code' => $d['code'],
                    'name' => ['tr' => $d['tr'], 'en' => $d['en']],
                    'sort_order' => $d['sort'],
                    'is_active' => true,
                ]
            );
        }

        // --- OTORİTELER (AUTHORITY) ---
        $authorities = [
            ['code' => 'ROM-EMP', 'tr' => 'Roma İmparatorluğu',   'en' => 'Roman Empire',       'sort' => 1],
            ['code' => 'MAC-KG',  'tr' => 'Makedonya Krallığı',   'en' => 'Kingdom of Macedon', 'sort' => 2],
            ['code' => 'SEL-EMP', 'tr' => 'Seleukos İmparatorluğu', 'en' => 'Seleucid Empire',   'sort' => 3],
            ['code' => 'ATT-KG',  'tr' => 'Attalos Krallığı',      'en' => 'Attalid Kingdom',    'sort' => 4],
            ['code' => 'BYZ-EMP', 'tr' => 'Bizans İmparatorluğu',  'en' => 'Byzantine Empire',   'sort' => 5],
        ];

        foreach ($authorities as $a) {
            Dictionary::updateOrCreate(
                ['type' => 'authority', 'name->tr' => $a['tr']],
                [
                    'code' => $a['code'],
                    'name' => ['tr' => $a['tr'], 'en' => $a['en']],
                    'sort_order' => $a['sort'],
                    'is_active' => true,
                ]
            );
        }

        // --- HÜKÜMDARLAR (RULER) ---
        $rulers = [
            ['code' => 'AUG', 'tr' => 'Augustus',           'en' => 'Augustus',           'sort' => 1],
            ['code' => 'ALX3', 'tr' => 'Büyük İskender III', 'en' => 'Alexander III Great', 'sort' => 2],
            ['code' => 'SEP', 'tr' => 'Septimius Severus',  'en' => 'Septimius Severus',  'sort' => 3],
            ['code' => 'HAD', 'tr' => 'Hadrianus',          'en' => 'Hadrian',            'sort' => 4],
            ['code' => 'CON1', 'tr' => 'I. Konstantin',     'en' => 'Constantine I',      'sort' => 5],
            ['code' => 'JST1', 'tr' => 'I. Justinianus',    'en' => 'Justinian I',        'sort' => 6],
        ];

        foreach ($rulers as $r) {
            Dictionary::updateOrCreate(
                ['type' => 'ruler', 'name->tr' => $r['tr']],
                [
                    'code' => $r['code'],
                    'name' => ['tr' => $r['tr'], 'en' => $r['en']],
                    'sort_order' => $r['sort'],
                    'is_active' => true,
                ]
            );
        }

        // --- BÖLGELER (REGION) ---
        $regions = [
            ['code' => 'PAM', 'tr' => 'Pamphylia', 'en' => 'Pamphylia', 'sort' => 1],
            ['code' => 'PIS', 'tr' => 'Pisidia',   'en' => 'Pisidia',   'sort' => 2],
            ['code' => 'CIL', 'tr' => 'Cilicia',   'en' => 'Cilicia',   'sort' => 3],
            ['code' => 'CAR', 'tr' => 'Caria',     'en' => 'Caria',     'sort' => 4],
            ['code' => 'ION', 'tr' => 'Ionia',     'en' => 'Ionia',     'sort' => 5],
            ['code' => 'LYC', 'tr' => 'Lycia',     'en' => 'Lycia',     'sort' => 6],
            ['code' => 'MYS', 'tr' => 'Mysia',     'en' => 'Mysia',     'sort' => 7],
        ];

        foreach ($regions as $rg) {
            Dictionary::updateOrCreate(
                ['type' => 'region', 'name->tr' => $rg['tr']],
                [
                    'code' => $rg['code'],
                    'name' => ['tr' => $rg['tr'], 'en' => $rg['en']],
                    'sort_order' => $rg['sort'],
                    'is_active' => true,
                ]
            );
        }

        // --- DARPHANELER (MINT) ---
        $mints = [
            ['code' => 'SIDE', 'tr' => 'Side',           'en' => 'Side',           'sort' => 1],
            ['code' => 'PERG', 'tr' => 'Perge',          'en' => 'Perge',          'sort' => 2],
            ['code' => 'EPH',  'tr' => 'Ephesos',        'en' => 'Ephesus',        'sort' => 3],
            ['code' => 'ROM',  'tr' => 'Roma',           'en' => 'Rome',           'sort' => 4],
            ['code' => 'ANT',  'tr' => 'Antiochia',      'en' => 'Antioch',        'sort' => 5],
            ['code' => 'CON',  'tr' => 'Constantinople', 'en' => 'Constantinople', 'sort' => 6],
            ['code' => 'PERGM', 'tr' => 'Pergamum',       'en' => 'Pergamum',       'sort' => 7],
        ];

        foreach ($mints as $mn) {
            Dictionary::updateOrCreate(
                ['type' => 'mint', 'name->tr' => $mn['tr']],
                [
                    'code' => $mn['code'],
                    'name' => ['tr' => $mn['tr'], 'en' => $mn['en']],
                    'sort_order' => $mn['sort'],
                    'is_active' => true,
                ]
            );
        }
    }
}

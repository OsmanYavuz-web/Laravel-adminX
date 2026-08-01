<?php

namespace Database\Seeders;

use App\Modules\ExcaCoin\Models\Coin;
use App\Modules\ExcaCoin\Models\Dictionary;
use App\Modules\ExcaCoin\Models\ExcavationProject;
use App\Modules\ExcaCoin\Models\Find;
use App\Models\User;
use Illuminate\Database\Seeder;

class ExcaCoinSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@admin.com')->first() ?? User::first() ?? User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

        // Sözlük ID'lerini al
        $getDictId = fn ($type, $trName) => Dictionary::where('type', $type)->where('name->tr', $trName)->value('id');

        $periodImperial = $getDictId('period', 'Roma İmparatorluk');
        $periodHellenic = $getDictId('period', 'Hellenistik');
        $periodByzantine = $getDictId('period', 'Bizans');

        $metalBronze = $getDictId('metal', 'Bronz / Bakır');
        $metalSilver = $getDictId('metal', 'Gümüş');
        $metalGold = $getDictId('metal', 'Altın');

        $denomSestertius = $getDictId('denomination', 'Sestertius');
        $denomTetradrachm = $getDictId('denomination', 'Tetradrachm');
        $denomSolidus = $getDictId('denomination', 'Solidus');
        $denomDenarius = $getDictId('denomination', 'Denarius');

        $authRome = $getDictId('authority', 'Roma İmparatorluğu');
        $authMacedon = $getDictId('authority', 'Makedonya Krallığı');
        $authByzantine = $getDictId('authority', 'Bizans İmparatorluğu');

        $rulerAugustus = $getDictId('ruler', 'Augustus');
        $rulerAlexander = $getDictId('ruler', 'Büyük İskender III');
        $rulerSeptimius = $getDictId('ruler', 'Septimius Severus');
        $rulerConstantine = $getDictId('ruler', 'I. Konstantin');
        $rulerJustinian = $getDictId('ruler', 'I. Justinianus');

        $regionPamphylia = $getDictId('region', 'Pamphylia');
        $regionIonia = $getDictId('region', 'Ionia');

        $mintSide = $getDictId('mint', 'Side');
        $mintPerge = $getDictId('mint', 'Perge');
        $mintEphesus = $getDictId('mint', 'Ephesos');
        $mintRome = $getDictId('mint', 'Roma');
        $mintConstantinople = $getDictId('mint', 'Constantinople');

        // ── 1. PROJELER ──
        $project1 = ExcavationProject::updateOrCreate(
            ['name' => 'Side Antik Kenti Kazısı 2024'],
            [
                'site_name' => 'Side Antik Kenti',
                'location' => 'Antalya, Manavgat',
                'country' => 'Türkiye',
                'start_date' => '1947-05-01',
                'end_date' => null,
                'director' => 'Prof. Dr. Feriştah Alanyalı',
                'description' => 'Side antik kentinde yürütülen sistemli arkeolojik kazı ve koruma çalışmaları.',
                'is_active' => true,
                'created_by' => $admin->id,
            ]
        );

        $project2 = ExcavationProject::updateOrCreate(
            ['name' => 'Perge Antik Kenti Kazısı 2024'],
            [
                'site_name' => 'Perge Antik Kenti',
                'location' => 'Antalya, Aksu',
                'country' => 'Türkiye',
                'start_date' => '1946-06-01',
                'end_date' => null,
                'director' => 'Prof. Dr. Sedef Çokay Kepçe',
                'description' => 'Perge stadyumu ve güney kapısı civarında yürütülen kazı çalışmaları.',
                'is_active' => true,
                'created_by' => $admin->id,
            ]
        );

        $project3 = ExcavationProject::updateOrCreate(
            ['name' => 'Ephesos Antik Kenti Kazısı 2024'],
            [
                'site_name' => 'Ephesos Antik Kenti',
                'location' => 'İzmir, Selçuk',
                'country' => 'Türkiye',
                'start_date' => '1895-09-01',
                'end_date' => null,
                'director' => 'Prof. Dr. Sabine Ladstätter',
                'description' => 'Ephesos Yamaç Evler ve Tiyatro caddesi numizmatik buluntu envanter çalışması.',
                'is_active' => true,
                'created_by' => $admin->id,
            ]
        );

        // ── 2. BULUNTULAR ──
        $find1 = Find::updateOrCreate(
            ['inventory_number' => 'SIDE-2024-F001', 'excavation_project_id' => $project1->id],
            [
                'find_date' => '2024-05-14',
                'excavation_area' => 'Kuzey Cadde',
                'excavation_season' => '2024',
                'sector' => 'Sektör A (Agora)',
                'area' => 'Alan 3',
                'trench' => 'Açma A-12',
                'square' => 'Kare N-14',
                'sub_square' => 'Alt Kare 2',
                'locus' => 'LOC-104',
                'context' => 'Agora Dükkan 4 Taban Altı Dolgusu',
                'stratigraphic_unit' => 'US-104',
                'unit' => 'Birim 2',
                'layer' => 'Tabaka III',
                'level' => 'Seviye -1.45m',
                'phase' => 'Faz 2 (Roma Dönemi)',
                'feature' => 'Mermer Döşeme Altı Yapısı',
                'grave_number' => null,
                'structure' => 'Kuzey Stoa Dükkanı',
                'room' => 'Oda 4',
                'architectural_feature' => 'Mozaikli Taban Kurulumu',
                'find_spot' => 'Kuzeydoğu Köşesi, Harçlı Tabaka Üzeri',
                'elevation' => 14.85,
                'coordinate_x' => 36.767500,
                'coordinate_y' => 31.391200,
                'coordinate_z' => 12.500,
                'find_number' => 'FN-045',
                'bag_number' => 'BAG-102',
                'find_group' => 'Sikke Definesi',
                'find_note' => 'Mermer plaka altında bronz sikkeler toplu halde ele geçirildi, koruma durumu oldukça iyi.',
                'created_by' => $admin->id,
            ]
        );

        $find2 = Find::updateOrCreate(
            ['inventory_number' => 'SIDE-2024-F002', 'excavation_project_id' => $project1->id],
            [
                'find_date' => '2024-06-02',
                'excavation_area' => 'Tiyatro Caddesi',
                'excavation_season' => '2024',
                'sector' => 'Sektör B (Tiyatro)',
                'area' => 'Alan 1',
                'trench' => 'Açma T-04',
                'square' => 'Kare K-08',
                'sub_square' => 'Alt Kare 1',
                'locus' => 'LOC-202',
                'context' => 'Sarnıç İç Dolgusu',
                'stratigraphic_unit' => 'US-208',
                'unit' => 'Birim 5',
                'layer' => 'Tabaka IV',
                'level' => 'Seviye -2.10m',
                'phase' => 'Faz 3 (Geç Antik)',
                'feature' => 'Su Kanalı Mantosu',
                'grave_number' => null,
                'structure' => 'Güney Sarnıcı',
                'room' => 'Hazne 1',
                'architectural_feature' => 'Kemer Kasnağı Altı',
                'find_spot' => 'Güney Duvarı Dibi, Mil Tabakası İçinde',
                'elevation' => 11.20,
                'coordinate_x' => 36.766100,
                'coordinate_y' => 31.390500,
                'coordinate_z' => 9.800,
                'find_number' => 'FN-089',
                'bag_number' => 'BAG-204',
                'find_group' => 'Tekil Buluntu',
                'find_note' => 'Sarnıç mil birikintisi temizlenirken bulunan gümüş denarius.',
                'created_by' => $admin->id,
            ]
        );

        $find3 = Find::updateOrCreate(
            ['inventory_number' => 'PERGE-2024-F001', 'excavation_project_id' => $project2->id],
            [
                'find_date' => '2024-06-20',
                'excavation_area' => 'Güney Kapısı',
                'excavation_season' => '2024',
                'sector' => 'Sektör K (Kapı Kompleksi)',
                'area' => 'Kule A',
                'trench' => 'Açma K-01',
                'square' => 'Kare P-05',
                'sub_square' => 'Alt Kare 4',
                'locus' => 'LOC-305',
                'context' => 'Anıtsal Kapı Giriş Tabakası',
                'stratigraphic_unit' => 'US-310',
                'unit' => 'Birim 1',
                'layer' => 'Tabaka II',
                'level' => 'Seviye -0.80m',
                'phase' => 'Faz 1 (Hellenistik)',
                'feature' => 'Kule Temel Bloku',
                'grave_number' => null,
                'structure' => 'Hellenistik Yuvarlak Kule',
                'room' => 'Giriş Holü',
                'architectural_feature' => 'Profilatör Taş Üzeri',
                'find_spot' => 'Doğu Duvar Taban Hattı',
                'elevation' => 22.40,
                'coordinate_x' => 36.960800,
                'coordinate_y' => 30.852100,
                'coordinate_z' => 20.100,
                'find_number' => 'FN-112',
                'bag_number' => 'BAG-301',
                'find_group' => 'Temel Adak Buluntusu',
                'find_note' => 'Yuvarlak kule temel harcı tabakasında adak sikkesi olarak yerleştirilmiş tetradrahmi.',
                'created_by' => $admin->id,
            ]
        );

        $find4 = Find::updateOrCreate(
            ['inventory_number' => 'EPHESOS-2024-F001', 'excavation_project_id' => $project3->id],
            [
                'find_date' => '2024-07-08',
                'excavation_area' => 'Yamaç Evler 2',
                'excavation_season' => '2024',
                'sector' => 'Sektör YE (Yamaç Konutları)',
                'area' => 'Insula 6',
                'trench' => 'Açma YE-12',
                'square' => 'Kare E-02',
                'sub_square' => 'Alt Kare 3',
                'locus' => 'LOC-501',
                'context' => 'Peristilli Avlu Havuz Tabakası',
                'stratigraphic_unit' => 'US-505',
                'unit' => 'Birim 3',
                'layer' => 'Tabaka V (Bizans Tahrip)',
                'level' => 'Seviye -3.50m',
                'phase' => 'Faz 4 (Erken Bizans)',
                'feature' => 'Havuz Mermer Kaplaması',
                'grave_number' => null,
                'structure' => 'Zengin Konutu 2',
                'room' => 'Peristil Avlu',
                'architectural_feature' => 'Freskli Duvar Tabanı',
                'find_spot' => 'Havuz Su Tahliye Deliği Yakını',
                'elevation' => 45.30,
                'coordinate_x' => 37.940500,
                'coordinate_y' => 27.341200,
                'coordinate_z' => 42.000,
                'find_number' => 'FN-230',
                'bag_number' => 'BAG-510',
                'find_group' => 'Yangın Tabakası Buluntusu',
                'find_note' => 'MS 7. yüzyıl yangın tabakasında kömürleşmiş ahşap dolgusu içinde bulundu.',
                'created_by' => $admin->id,
            ]
        );

        // ── 3. SİKKELER ──
        // Sikke 1: Augustus Sestertius (Side 001)
        Coin::updateOrCreate(
            ['reference' => 'RIC II Augustus 207; RPC I 3381', 'find_id' => $find1->id],
            [
                'excavation_project_id' => $project1->id,
                'period_id' => $periodImperial,
                'authority_id' => $authRome,
                'ruler_id' => $rulerAugustus,
                'region_id' => $regionPamphylia,
                'mint_id' => $mintSide,
                'metal_id' => $metalBronze,
                'denomination_id' => $denomSestertius,
                'date_range' => 'MÖ 27 - MS 14',
                'diameter' => 32.50,
                'weight' => 25.400,
                'axis' => 12,
                'is_cut' => false,
                'is_pierced' => false,
                'obverse_description' => 'Defne çelenkli Augustus büstü sağa bakıyor.',
                'obverse_legend' => 'CAESAR AVGVSTVS DIVI F PATER PATRIAE',
                'obverse_legend_expanded' => 'Caesar Augustus Divi Filius Pater Patriae',
                'reverse_description' => 'Altar üzerinde defne dalı ve çelenk figürleri, ortada S C.',
                'reverse_legend' => 'PONTIF MAXIM TRIBVNIC POT',
                'reverse_legend_expanded' => 'Pontifex Maximus Tribunicia Potestate',
                'mint_mark' => 'S C',
                'magistrate' => 'P. Lurius Agrippa',
                'control_mark' => 'A',
                'monogram' => 'MON-01',
                'countermark' => 'CM-NC',
                'is_overstrike' => false,
                'note' => 'Mükemmel kondisyonda, zeytin yeşili patinalı nadir Side eyalet basımı örnek.',
                'created_by' => $admin->id,
            ]
        );

        // Sikke 2: Septimius Severus Denarius (Side 002)
        Coin::updateOrCreate(
            ['reference' => 'RIC IV Septimius Severus 150', 'find_id' => $find2->id],
            [
                'excavation_project_id' => $project1->id,
                'period_id' => $periodImperial,
                'authority_id' => $authRome,
                'ruler_id' => $rulerSeptimius,
                'region_id' => $regionPamphylia,
                'mint_id' => $mintRome,
                'metal_id' => $metalSilver,
                'denomination_id' => $denomDenarius,
                'date_range' => 'MS 193 - 211',
                'diameter' => 19.20,
                'weight' => 3.450,
                'axis' => 6,
                'is_cut' => false,
                'is_pierced' => false,
                'obverse_description' => 'Işın tacı giymiş Septimius Severus büstü sağa bakıyor.',
                'obverse_legend' => 'SEVERVS PIVS AVG',
                'obverse_legend_expanded' => 'Severus Pius Augustus',
                'reverse_description' => 'Victoria ayakta duruyor, sağ elinde çelenk, sol elinde palmiye dalı tutuyor.',
                'reverse_legend' => 'VICT PART MAX',
                'reverse_legend_expanded' => 'Victoria Parthica Maxima',
                'mint_mark' => 'ROM',
                'magistrate' => null,
                'control_mark' => 'VI',
                'monogram' => null,
                'countermark' => null,
                'is_overstrike' => false,
                'note' => 'Parth zaferi anısına basılmış gümüş denarius, parıltılı yüzey.',
                'created_by' => $admin->id,
            ]
        );

        // Sikke 3: Büyük İskender Tetradrahmi (Perge 001)
        Coin::updateOrCreate(
            ['reference' => 'Price 2814; Muller 1245', 'find_id' => $find3->id],
            [
                'excavation_project_id' => $project2->id,
                'period_id' => $periodHellenic,
                'authority_id' => $authMacedon,
                'ruler_id' => $rulerAlexander,
                'region_id' => $regionPamphylia,
                'mint_id' => $mintPerge,
                'metal_id' => $metalSilver,
                'denomination_id' => $denomTetradrachm,
                'date_range' => 'MÖ 336 - MÖ 323',
                'diameter' => 28.80,
                'weight' => 17.150,
                'axis' => 12,
                'is_cut' => false,
                'is_pierced' => false,
                'obverse_description' => 'Aslan postu giymiş Genç Herakles başı sağa bakıyor.',
                'obverse_legend' => null,
                'obverse_legend_expanded' => null,
                'reverse_description' => 'Zeus Aetophoros tahtta oturuyor, sağ elinde kartal, sol elinde asa tutuyor. Solda Perge darphane simgesi Artemis heykelciği.',
                'reverse_legend' => 'AΛEΞANΔPOY',
                'reverse_legend_expanded' => 'Alexandrou',
                'mint_mark' => 'K',
                'magistrate' => 'Herakleidas',
                'control_mark' => 'Sphinx Monogram',
                'monogram' => 'MON-ALX',
                'countermark' => null,
                'is_overstrike' => false,
                'note' => 'Perge darphanesinin erken Hellenistik donem anitsal gümüş tetradrahmi örneği.',
                'created_by' => $admin->id,
            ]
        );

        // Sikke 4: I. Justinianus Altın Solidus (Ephesos 001)
        Coin::updateOrCreate(
            ['reference' => 'DOC I 9; MIBE 7; Sear 138', 'find_id' => $find4->id],
            [
                'excavation_project_id' => $project3->id,
                'period_id' => $periodByzantine,
                'authority_id' => $authByzantine,
                'ruler_id' => $rulerJustinian,
                'region_id' => $regionIonia,
                'mint_id' => $mintConstantinople,
                'metal_id' => $metalGold,
                'denomination_id' => $denomSolidus,
                'date_range' => 'MS 527 - 565',
                'diameter' => 20.50,
                'weight' => 4.480,
                'axis' => 6,
                'is_cut' => false,
                'is_pierced' => false,
                'obverse_description' => 'Zırhlı ve miğferli Justinianus cepheden büstü, sağ elinde globus cruciger tutuyor.',
                'obverse_legend' => 'D N IVSTINIANVS P P AVG',
                'obverse_legend_expanded' => 'Dominus Noster Justinianus Perpetuus Augustus',
                'reverse_description' => 'Angelus (Melek) cepheden ayakta duruyor, sağ elinde uzun haç, sol elinde globus cruciger tutuyor.',
                'reverse_legend' => 'VICTORIA AVCCC Z',
                'reverse_legend_expanded' => 'Victoria Augustorum Officina Zeta',
                'mint_mark' => 'CONOB',
                'magistrate' => null,
                'control_mark' => 'Z',
                'monogram' => 'CHRISTOGRAM',
                'countermark' => null,
                'is_overstrike' => false,
                'note' => 'Tam ayar Constantinople darplı altın solidus. Yangın tabakasından çıkarılmış yüksek kondisyonlu müze eseri.',
                'created_by' => $admin->id,
            ]
        );
    }
}

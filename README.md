# YKS MASTER

## Proje hakkında

YKS sınavına hazırlanırken günlük çalışma planımı takip etmeme yardımcı olacak bir web uygulaması geliştirdim. TYT ve AYT dersleri üzerinden plan yapabiliyor ve çalışmalarımı kaydedebiliyorum.

## Özellikler

- Kayıt, giriş ve öğrenci ile yönetici rolleri.
- Ders ve konu kataloğu ile arama.
- Günlük plan oluşturma, düzenleme ve silme.
- Çalışma durumu ve gerçekleşen süre kaydı.
- Plan işlemleri ve ders seçimi için REST API.
- Dinamik plan kartları ve istemci tarafında form doğrulaması.

## Kullanılan teknolojiler

PHP 8.2, PDO, MySQL veya MariaDB 10.4 (XAMPP), HTML, CSS ve JavaScript kullandım.

## Kurulum

1. XAMPP ile MySQL servisini başlatıp `yks_master` adlı utf8mb4 veritabanını oluşturuyorum.
2. `database/schema.sql` ve ardından `database/seed.sql` dosyalarını içe aktarıyorum.
3. `config/config.example.php` dosyasını `config/config.php` olarak kopyalayıp bağlantı bilgilerini düzenliyorum.
4. `php database/create_admin.php email@example.com "Ad Soyad"` komutuyla yönetici oluşturup parolayı terminale giriyorum.
5. `php -S localhost:8000 -t public public/index.php` komutuyla çalıştırıp `http://localhost:8000` adresini açıyorum.

Apache kullanırsam projeyi `htdocs` içine yerleştirip `public/` üzerinden açıyorum. `mod_rewrite` ve `AllowOverride All` açık olmalı; alt klasör için `config/config.php` içindeki `base_path` değerini örneğin `/yks/public` olarak ayarlıyorum.

## Klasör yapısı

- `src/`: yönlendirici, denetleyiciler, doğrulama ve veritabanı işlemleri.
- `views/`: sayfalar; `public/`: giriş noktası ve statik dosyalar.
- `database/`: şema, başlangıç verileri ve komutlar.
- `docs/`: proje ve API belgeleri.

## Sayfalar ve roller

Misafirler ana sayfayı, kayıt ve giriş sayfalarını kullanır. Öğrenciler Bugün ve Katalog sayfalarına erişir. Yönetici giriş yapıp katalog sayfasını kullanabilir.

## API

İstek ve yanıt örneklerini [API belgesinde](docs/API.md) açıkladım.

## Geliştirme devam ediyor

- Haftalık özet
- Konu ilerlemesi
- Çalışma geçmişi
- Profil
- Yönetici paneli
- Testler

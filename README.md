Modern File Manager & Image to WebP Converter
Bu proje, PHP ile geliştirilmiş modern bir dosya yöneticisi ve resim dönüştürücü sistemidir. Resim dosyalarını otomatik olarak WebP formatına dönüştürür ve optimize eder.

🚀 Özellikler
📁 Dosya Yönetimi
Çoklu dosya yükleme

Dosya listeleme ve görüntüleme

Dosya silme

Link paylaşımı (kopyalama)

Dosya indirme

Responsive tasarım

🖼️ Resim İşleme
Otomatik WebP Dönüşümü: Yüklenen resimler otomatik olarak WebP formatına dönüştürülür

Boyut Optimizasyonu: Resimler 895x595 piksel boyutuna optimize edilir

Thumbnail Oluşturma: Her resim için 200x200 piksel thumbnail oluşturulur

Desteklenen Formatlar: JPG, JPEG, PNG, GIF, BMP → WebP

🔒 Güvenlik
Şifre korumalı giriş sistemi

Dosya isimlerini rastgele oluşturma

Session tabanlı kimlik doğrulama

Güvenli dosya işlemleri

🎨 Kullanıcı Arayüzü
Açık/Koyu tema desteği

Modern ve responsive tasarım

Drag & Drop dosya yükleme

Resim önizleme modal'ı

Yükleme animasyonları

Kopyalama bildirimleri

📦 Kurulum
Gereksinimler:

PHP 7.4 veya üzeri

GD Kütüphanesi (resim işleme için)

WebP desteği

Kurulum Adımları:

bash
# Projeyi klonlayın veya dosyaları sunucunuza yükleyin
git clone [repo-url]
cd [project-folder]

# Dosya izinlerini ayarlayın
chmod 755 files/
chmod 755 files/thumbs/

# Şifreyi değiştirin
# index.php dosyasında $PASSWORD değişkenini güncelleyin
Yapılandırma:

$PASSWORD değişkenini güçlü bir şifre ile değiştirin

$UPLOAD_DIR ve $THUMB_DIR yollarını ihtiyacınıza göre düzenleyin

Dosya boyut limitlerini sunucu ayarlarınızdan yapılandırın

🛠️ Kullanım
Giriş Yapma
Tarayıcıdan index.php dosyasını açın

Belirlediğiniz şifreyi girin

Dosya yönetim paneline erişin

Dosya Yükleme
Dosya seç butonuna tıklayın veya dosyaları sürükleyip bırakın

Birden fazla dosya seçebilirsiniz

Yükle butonuna tıklayın

Resimler otomatik olarak WebP'ye dönüştürülecek ve optimize edilecektir

Dosya Yönetimi
İndir: ⬇️ butonu ile dosyayı indirin

Paylaş: 🔗 butonu ile dosya linkini kopyalayın

Sil: 🗑️ butonu ile dosyayı silin

Önizleme: Thumbnail'e tıklayarak resmi büyük görebilirsiniz

🔧 Teknik Detaylar
Resim İşleme Özellikleri
Boyutlandırma: Resimler 895x595 piksele orantılı olarak küçültülür

Kalite: WebP dosyaları %85 kalitede kaydedilir

Thumbnail: 200x200 piksel boyutunda thumbnail'ler oluşturulur

Şeffaflık: PNG ve GIF dosyalarının şeffaflığı korunur

Dosya İsimlendirme
Her dosya için 12 karakterlik rastgele string oluşturulur

Format: [random12char]-nornaio.[extension]

Örnek: aB3dEfG5hIjK-nornaio.webp

Güvenlik Önlemleri
XSS koruması (htmlspecialchars)

Dosya yol traversal koruması (basename)

Session hijacking koruması

Şifre hash'leme (geliştirilebilir)

🌐 Hosting Önerileri
Shared Hosting
cPanel/Plesk panel üzerinden PHP ve GD kütüphanesini etkinleştirin

Dosya yükleme limitini artırın (php.ini veya .htaccess)

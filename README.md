# Hastane Randevu Sistemi 🏥

Modern ve kullanıcı dostu bir hastane randevu yönetim sistemi. Bu proje, hastaların kolayca randevu alabilmesi ve hastane personelinin randevuları etkin bir şekilde yönetebilmesi için geliştirilmiştir.

## 📋 İçindekiler

- [Özellikler](#özellikler)
- [Teknolojiler](#teknolojiler)
- [Kurulum](#kurulum)  
- [Kullanım](#kullanım)
- [API Dokümantasyonu](#api-dokümantasyonu)
- [Katkıda Bulunma](#katkıda-bulunma)
- [Lisans](#lisans)

## ✨ Özellikler

### Hasta Özellikleri
- 👤 Kullanıcı kayıt ve giriş sistemi
- 📅 Uygun tarihlerde randevu alma
- 👨‍⚕️ Doktor ve bölüm seçimi
- 📱 Randevu durumu takibi
- ✏️ Randevu iptal etme/güncelleme
- 📧 E-posta bildirimleri

### Admin/Personel Özellikleri
- 🏥 Hastane yönetim paneli
- 👩‍⚕️ Doktor bilgileri yönetimi
- 📊 Randevu raporları ve istatistikler
- ⏰ Çalışma saatleri ayarlama
- 🚫 Randevu onaylama/iptal etme

### Genel Özellikler
- 📱 Responsive tasarım (mobil uyumlu)
- 🔒 Güvenli kimlik doğrulama
- 🌐 Çok dilli destek
- 🎨 Modern ve sezgisel kullanıcı arayüzü

## 🛠 Teknolojiler

### Backend
- **Framework:** PHP/Laravel
- **Veritabanı:** MySQL
- **Kimlik Doğrulama:** JWT Token tabanlı güvenlik

### Frontend  
- **Styling:** CSS framework 

### DevOps
- **Version Control:** Git & GitHub

## 🚀 Kurulum

### Gereksinimler
- PHP (7.4+)
- MySQL
- pip/composer

### Adım 1: Repository'yi Klonlayın
```bash
git clone https://github.com/nesihx/hospital_appointment_system.git
cd hospital_appointment_system
```

### Adım 2: Bağımlılıkları Yükleyin
```bash
# Backend için
npm install
# veya
pip install -r requirements.txt

# Frontend için (eğer ayrı bir klasörde ise)
cd frontend
npm install
```

### Adım 3: Veritabanını Ayarlayın
```bash
# Veritabanı oluşturun
mysql -u root -p
CREATE DATABASE hospital_appointment_db;

# Migration'ları çalıştırın
npm run migrate
# veya
python manage.py migrate
```

### Adım 4: Çevre Değişkenlerini Yapılandırın
`.env` dosyasını oluşturun ve aşağıdaki bilgileri ekleyin:
```env
DB_HOST=localhost
DB_PORT=3306
DB_NAME=hospital_appointment_db
DB_USER=your_username
DB_PASSWORD=your_password

JWT_SECRET=your_jwt_secret_key
EMAIL_HOST=smtp.gmail.com
EMAIL_PORT=587
EMAIL_USER=your_email@gmail.com
EMAIL_PASS=your_email_password
```

### Adım 5: Uygulamayı Başlatın
```bash
# Development modunda
npm run dev
# veya
python manage.py runserver

# Production modunda
npm start
```

Uygulama varsayılan olarak `http://localhost` adresinde çalışacaktır.

## 📖 Kullanım

### Hasta Olarak Randevu Alma
1. Ana sayfadan "Kayıt Ol" seçeneğini tıklayın
2. Gerekli bilgileri doldurun ve hesabınızı oluşturun
3. Giriş yapın ve "Randevu Al" butonunu tıklayın
4. Bölüm ve doktor seçin
5. Uygun tarih ve saati seçin
6. Randevu detaylarını onaylayın

### Admin Olarak Sistem Yönetimi
1. Admin paneline giriş yapın (`/admin`)
2. Sol menüden ilgili bölümü seçin
3. Doktor ekle/düzenle/sil işlemlerini gerçekleştirin
4. Randevu onaylama/iptal işlemlerini yapın
5. Raporları görüntüleyin

## 📚 API Dokümantasyonu

### Kimlik Doğrulama
```http
POST /api/auth/register
POST /api/auth/login
POST /api/auth/logout
```

### Randevu İşlemleri
```http
GET    /api/appointments          # Tüm randevuları listele
POST   /api/appointments          # Yeni randevu oluştur
GET    /api/appointments/:id      # Belirli randevuyu getir
PUT    /api/appointments/:id      # Randevuyu güncelle
DELETE /api/appointments/:id      # Randevuyu iptal et
```

### Doktor İşlemleri
```http
GET    /api/doctors               # Tüm doktorları listele
GET    /api/doctors/:id           # Belirli doktoru getir
GET    /api/doctors/:id/schedule  # Doktorun müsait saatlerini getir
```

Detaylı API dokümantasyonu için [API Docs](./docs/api.md) dosyasını inceleyiniz.

## 🧪 Testler

```bash
# Tüm testleri çalıştır
npm test

# Test coverage raporu
npm run test:coverage

# Belirli bir test dosyasını çalıştır
npm test -- --grep "appointment"
```


## 🤝 Katkıda Bulunma

Bu projeye katkıda bulunmak isteyenler için:

1. Bu repository'yi fork edin
2. Feature branch oluşturun (`git checkout -b feature/AmazingFeature`)
3. Değişikliklerinizi commit edin (`git commit -m 'Add some AmazingFeature'`)
4. Branch'inizi push edin (`git push origin feature/AmazingFeature`)
5. Pull Request oluşturun

### Kod Standartları
- ESLint kurallarına uyun
- Commit mesajlarını anlamlı yazın
- Test coverage %80'in üzerinde tutun
- Kod yorumlarını Türkçe yazın


## 📄 Lisans

Bu proje [MIT Lisansı](LICENSE) altında lisanslanmıştır.

## 👨‍💻 Geliştirici

**Nesih** 
- GitHub: [@nesihx](https://github.com/nesihx)

## 📞 İletişim

Sorularınız için:
- 📧 E-posta: [nesihkardas01l@gmail.com]
- 💬 Issue açarak GitHub üzerinden iletişime geçebilirsiniz

## 🙏 Teşekkürler

Bu projenin geliştirilmesinde yardımcı olan herkese teşekkürler!

---

⭐ Bu projeyi beğendiyseniz yıldız vermeyi unutmayın!

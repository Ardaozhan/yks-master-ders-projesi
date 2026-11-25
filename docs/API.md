# API

Planlar için oturum açmış öğrenci hesabı kullanıyorum. İstek ve yanıtlar UTF-8 JSON biçimindedir. Değişiklik isteklerinde `Content-Type: application/json` ve sayfadaki `csrf-token` meta değerini `X-CSRF-Token` başlığıyla gönderiyorum. `/api/konular` için giriş yapmış olmak yeterlidir.

Başarılı yanıt örneği: `{"status":"ok","data":[],"error":null,"errors":null}`. Hata örneği: `{"status":"error","data":null,"error":"Alanları kontrol edin.","errors":{"target_minutes":"Geçersiz süre"}}`.

| Yöntem ve yol | Kullanımı | Başarı | Hatalar |
| --- | --- | --- | --- |
| GET /api/plans?tarih=2026-11-22 | Günün planları; alternatif olarak `baslangic` ve `bitis` | 200, liste | 400 geçersiz tarih |
| GET /api/plans/1 | Bana ait plan | 200, nesne | 404 bulunamadı |
| POST /api/plans | Plan oluşturma | 201, nesne ve Location | 400, 415, 422 |
| PUT /api/plans/1 | Plan alanlarını değiştirme | 200, nesne | 400, 404, 415, 422 |
| PATCH /api/plans/1/status | Çalışma durumunu değiştirme | 200, nesne | 400, 404, 415, 422 |
| DELETE /api/plans/1 | Planı silme | 204, boş yanıt | 404 |
| GET /api/subjects?tur=TYT | Etkin dersler | 200, liste | 400 |
| GET /api/konular?ders=1 | Etkin konular | 200, liste | 400 |

Giriş yoksa 401, yetki veya güvenlik belirteci uygun değilse 403 döner. Yanlış yöntem için 405 ve `Allow` başlığı, sunucu hatası için 500 döner. 400 geçersiz JSON, 415 yanlış içerik türü, 422 alan doğrulama hatasıdır. Başka bir öğrencinin planı da 404 döner.

POST ve PUT gövdesi örneği:

```json
{"date":"2026-11-22","type":"TYT","subject_id":1,"topic_id":1,"target_minutes":60,"note":"Tekrar"}
```

PATCH gövdesi örneği: `{"status":"completed","actual_minutes":45,"note":"Tamamlandı"}`. Durum değerleri `planned`, `in_progress` ve `completed`; tamamlanan çalışma için süre zorunludur.

Plan yanıtındaki `data` örneği:

```json
{"id":1,"date":"2026-11-22","exam_type":"TYT","subject":{"id":1,"name":"Matematik"},"topic":{"id":1,"name":"Konu"},"target_minutes":60,"status":"planned","actual_minutes":null,"note":"Tekrar","created_at":"2026-11-22 12:00:00","updated_at":"2026-11-22 12:00:00"}
```

Oturum çerezi ve belirteci aldıktan sonra örnek istek:

```bash
curl -X POST http://localhost:8000/api/plans -b "PHPSESSID=OTURUM" -H "Content-Type: application/json" -H "X-CSRF-Token: BELIRTEC" -d '{"date":"2026-11-22","type":"TYT","subject_id":1,"topic_id":1,"target_minutes":60,"note":"Tekrar"}'
```

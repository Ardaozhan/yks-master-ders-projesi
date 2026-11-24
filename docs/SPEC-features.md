# SPEC: YKS MASTER features (slices 2-5)

Builds on `docs/SPEC-skeleton.md` (same stack, layout, security rules, Router/Middleware, `e()`, CSRF on every POST,
PSR-12, Turkish UI). Requirement IDs refer to `YKS_Gereksinim_Analizi.docx`.

## Global rules

- **Ownership (FR-28, FR-38, NFR-03):** every student query filters by `user_id = Auth::id()` in SQL. A plan id that
  does not exist or belongs to someone else returns the same 404 view (no existence leak).
- **Durations:** stored as integer minutes. Shown as "1 sa 30 dk" via one helper `formatMinutes()`. Target (hedef) and
  actual (gerçekleşen) always labelled separately (NFR-09).
- **Week:** Monday-Sunday, ISO week. `?hafta=YYYY-MM-DD` = any day in the week; default current week. Header shows
  "17 Kasım - 23 Kasım 2026".
- **Dates:** input `YYYY-MM-DD`, shown `d.m.Y` with Turkish month names where text is used.
- **Status labels:** planned=Planlandı, in_progress=Devam ediyor, completed=Tamamlandı; topic: not_started=Çalışılmadı,
  in_progress=Çalışılıyor, done=Tamamlandı. Always text + badge, never color only.
- **Pagination:** 20 rows per page where lists can grow (history, admin).
- Flash success message after every write; validation errors next to fields with old input kept.
- Inactive subjects/topics are hidden from pickers and the student catalog but still shown in existing plans/history.

## Tour A: daily plan + study record (FR-06..11, FR-16..20)

| Method | Path | Handler | Guard |
|---|---|---|---|
| GET | /bugun?tarih=YYYY-MM-DD | Plan@today | auth, role:student |
| GET | /plan/yeni?tarih= | Plan@create | auth, role:student |
| POST | /plan | Plan@store | auth, role:student, csrf |
| GET | /plan/duzenle?id= | Plan@edit | auth, role:student |
| POST | /plan/guncelle | Plan@update | auth, role:student, csrf |
| POST | /plan/sil | Plan@delete | auth, role:student, csrf |
| POST | /plan/durum | Plan@status | auth, role:student, csrf |

- **Bugün page:** date header with previous/next day links and a date input; plan cards (exam type, subject, topic,
  target, status, actual if any, note); actions: Düzenle, Sil (POST form, confirm text names the topic), status change
  form. Empty day: message + "İlk planını ekle" button.
- **Plan form (create/edit):** date, TYT/AYT select, subject select, topic select (dependent, via /api/konular), target
  minutes (5-600), optional note (max 255). Server validates topic exists, is active (for new plans) and date is valid.
- **Status form:** status select + actual minutes (required and 1-600 when status=completed, optional for
  in_progress, cleared when back to planned) + note. Writes `study_plans.status` and upserts `study_records`
  (one row per plan, `studied_on` = plan_date). Completed plans stay editable (business rule).
- **Delete:** deletes the record row then the plan in one transaction.
- `updated_at` maintained on every change (FR-20).

## Tour B

### Weekly summary (FR-21..28) - GET /haftalik?hafta=
Week range header, previous/next week links. Cards: planned count, completed count, total target minutes, total
actual minutes (from study_records only). Table by subject: plans, completed, actual minutes. List of completed
studies grouped by subject -> topic. Empty week: "Bu hafta için kayıt yok" + link to /bugun. Daily/weekly switch links
(FR-25). Past weeks reachable (FR-27).

### Topic progress (FR-29..33) - GET /ilerleme?tur=&ders=, POST /ilerleme (csrf)
Per subject list of active topics with the student's status (missing row = not_started). Change status with a small
form per topic (upsert `student_topic_progress`, updated_at set). Undo = set back to not_started/in_progress (FR-32).
Per subject progress line "8/12 konu tamamlandı" (text, plus a bar with aria-label).

### History (FR-34..38) - GET /gecmis?baslangic=&bitis=&ders=&konu=&sayfa=, GET /gecmis/detay?id=
Past plans (plan_date <= today) newest first with date, subject, topic, status, target, actual. Filters: date range,
subject, topic; active filters shown with "Filtreleri temizle". No-result message distinct from "no data at all".
Detail page shows all fields incl. note, created_at, updated_at. Ownership rule applies.

### Profile (FR-03) - GET /profil, POST /profil (csrf)
Show and edit full_name and student_type; email shown read-only. Optional password change (current password required,
new password same rules as register).

### Admin catalog (FR-14, FR-39..45) - role:admin, all POST with csrf
| Method | Path | Purpose |
|---|---|---|
| GET | /yonetim?tur=&ders=&q=&sayfa= | Subject list filtered by type/search; selected subject's topics |
| POST | /yonetim/ders | Create subject (exam_type, name 2-100, sort_order) |
| POST | /yonetim/ders/guncelle | Update subject |
| POST | /yonetim/konu | Create topic under subject |
| POST | /yonetim/konu/guncelle | Update topic |
| POST | /yonetim/durum | Toggle is_active for subject or topic (`type=subject|topic`, id) |

No hard delete (FR-43). Deactivate button text explains "Geçmiş planlar korunur". Duplicate name within the same
subject/exam type is rejected with a field error. Success/error flash (FR-45).

### Navigation
Student: Bugün, Haftalık Özet, Konu İlerlemesi, Geçmiş, Katalog, Profil, Çıkış. Admin: Yönetim, Katalog, Çıkış.
Current page marked with aria-current.

## Tests (add to tests/run.php)
- formatMinutes, week range calculation (Monday start, year boundary 2026-12-31), plan validation rules, status rule
  (completed requires actual minutes).
- DB-backed (run when config/config.php exists, inside a transaction rolled back at the end): create plan, ownership
  query returns null for another user, weekly totals equal the sum of inserted actual minutes, deactivated topic still
  joins in history.

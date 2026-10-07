# Rombak Public UI — Professional Minimalist Full-width

Preferensi: Inter 12–25px, teal `#007774` + lime dipertahankan, edge-to-edge full, scope semua halaman publik. Hanya CSS/HTML, logic/token/signed/throttle tidak diubah.

## Checklist
- [x] 0. Token: Inter, skala 12–25, teal/lime (`resources/css/app.css`)
- [x] 1. Komponen `components/ui/*` (card, button, input, textarea, select, checkbox, radio, badge, alert, page-hero)
- [x] 2. `layouts/public` (navbar+footer full-width) + `layouts/guest` + `layouts/test` baru
- [x] 3. Karier `index` + `show`
- [x] 4. `apply` wizard + `confirmation` + `status`
- [x] 5. `tes` + `tes-disc` + `tes-mbti`
- [x] 6. Offering (5 view) + `login` + `403`
- [x] QA: `view:cache` OK, `pint` passed, `npm run build` OK. `php artisan test` gagal pre-existing (PHP tanpa driver sqlite, phpunit pakai sqlite :memory:) — tidak terkait perubahan blade/css.

## Aturan
- 1 font: Inter saja. Ukuran: 12 label/eyebrow, 13 meta/bantuan, 14 body/button, 15 lede ringkas, 16 ringkasan, 20 judul seksi, 25 judul halaman (maks).
- Full-width: `w-full px-4 sm:px-6 lg:px-8`, tidak ada `max-w-* mx-auto` sempit kecuali form fokus (tetap full dengan inner full).
- Minimalist: card putih `border-line rounded-xl`, 1 aksen teal, lime hanya badge/sukses, shadow subtle.
- Tombol: primary teal kanan bawah / full-width di mobile, secondary outline kiri.
- Tidak ubah: nama route, nama input form, Alpine `testEngine/discEngine/mbtiEngine`, validasi, signed URL.

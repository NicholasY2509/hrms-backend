# Panduan Integrasi Frontend: Sistem Persetujuan (Approval)

Dokumentasi ini menjelaskan cara menggunakan API terbaru untuk modul **Approval Workflow** yang mendukung 4 kategori utama: **Pending**, **Upcoming**, **Ongoing**, dan **History**.

---

## 1. Daftar Endpoint
Semua endpoint berada di bawah prefix: `/api/v1/approval-workflow/portal/management/actions`

| Kategori | Endpoint | Deskripsi |
| :--- | :--- | :--- |
| **Pending** | `GET /` | Pengajuan yang menunggu tindakan Anda **saat ini** (Inbox). |
| **Upcoming**| `GET /upcoming` | Pengajuan di mana Anda adalah approver di **langkah mendatang**. |
| **Ongoing** | `GET /ongoing` | Semua pengajuan **aktif** (status pending) di mana Anda terlibat. |
| **History** | `GET /history` | Semua pengajuan yang sudah **selesai/final** (Approved/Rejected/Cancelled). |

---

## 2. Parameter Query (Global)
Keempat endpoint di atas mendukung parameter berikut:

*   `search` (string): Mencari berdasarkan **nomor referensi** atau **nama karyawan**.
*   `type` (string): Filter berdasarkan tipe model (e.g., `Overtime`, `UnpaidLeave`).
*   `per_page` (int): Jumlah data per halaman (default: 15).
*   `page` (int): Nomor halaman pagination.

---

## 3. Ringkasan Jumlah (Summary Counts)
Setiap request dari 4 endpoint di atas akan menyertakan objek `summary_counts` di level atas response. Gunakan data ini untuk memperbarui angka pada badge tab secara real-time.

```json
{
  "data": [...],
  "links": {...},
  "meta": {...},
  "summary_counts": {
    "pending": 5,
    "upcoming": 2,
    "ongoing": 12,
    "history": 89
  },
  "message": "Daftar persetujuan berhasil diambil.",
  "status": "success"
}
```

---

## 4. Field Penting dalam Objek `data`

| Field | Tipe | Deskripsi |
| :--- | :--- | :--- |
| `approvable_request_step_id` | `int\|null` | **ID Step yang harus digunakan** untuk API Approve/Reject. Jika `null`, berarti pengajuan ini hanya untuk dipantau (view-only). |
| `user_step_status` | `string` | Status langkah milik user tersebut (`pending`, `approved`, `rejected`). |
| `status` | `string` | Status **global** dari pengajuan tersebut (`pending`, `approved`, `rejected`). |
| `current_step_sequence` | `int` | Urutan langkah (sequence) yang sedang aktif saat ini secara global. |

---

## 5. Alur Implementasi Frontend

### A. Fetching Data Berdasarkan Tab
```typescript
const fetchApprovals = async (tab: 'pending' | 'upcoming' | 'ongoing' | 'history', params: any) => {
  const endpointMap = {
    pending: '/',
    ongoing: '/ongoing',
    upcoming: '/upcoming',
    history: '/history'
  };

  const response = await axios.get(`/api/v1/approval-workflow/portal/management/actions${endpointMap[tab]}`, { params });
  
  // Gunakan summary_counts untuk update UI Tab/Sidebar
  const counts = response.data.summary_counts;
  
  return response.data;
};
```

### B. Melakukan Persetujuan (Approve/Reject)
Gunakan field `approvable_request_step_id` sebagai parameter ID di URL.

```typescript
// Approve
const handleApprove = async (stepId: number, notes: string, attachment?: File) => {
  const formData = new FormData();
  formData.append('notes', notes);
  if (attachment) formData.append('attachment', attachment);

  await axios.post(`/api/v1/approval-workflow/portal/management/actions/${stepId}/approve`, formData);
};

// Reject
const handleReject = async (stepId: number, notes: string) => {
  await axios.post(`/api/v1/approval-workflow/portal/management/actions/${stepId}/reject`, { notes });
};
```

---

## 6. Tips UI
1.  **Badge Angka:** Gunakan `summary_counts.pending` untuk menampilkan notifikasi angka di menu navigasi.
2.  **Kondisi Tombol:**
    *   Tampilkan tombol Aksi (Approve/Reject) **hanya jika** `user_step_status === 'pending'` DAN `sequence === current_step_sequence`.
    *   Gunakan `approvable_request_step_id` sebagai identitas unik untuk modal aksi.
3.  **Refresh:** Lakukan fetch ulang setelah aksi berhasil agar jumlah angka di `summary_counts` tersinkronisasi kembali.

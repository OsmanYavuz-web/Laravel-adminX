# 04 — Media Library & Secure File Sharing

The Media Library (`/adminx/media`) provides drag-and-drop file management, live visual previews, and a secure public/password-protected file sharing subsystem.

---

## 🖼️ Media Management Features

- **Drag & Drop Uploads**: Support for images (PNG, JPG, WEBP, SVG), documents (PDF, DOCX, TXT), and archives (ZIP, RAR).
- **Interactive Modals**:
  - Click card or file thumbnail to open the detail & preview modal.
  - PDF files display a live, embedded interactive `iframe` viewer.
  - Images display high-resolution responsive previews.
- **Direct New Tab Preview**: Every file card includes a `target="_blank"` link for direct browser viewing.

---

## 🔗 Public & Password-Protected File Sharing

Click **"Paylaş / Share"** on any file card to generate a share link (`/share/{token}`).

### Share Options:
1. **Optional Password Protection**: Enable password protection to force recipients to enter a password before viewing or downloading.
2. **Expiration Periods**: Choose link validity (1 Day, 7 Days, 30 Days, or Indefinite).

---

## 📊 Access & Audit Logs per Share Link

Every active share link tracks user interactions:

- **Views Counter**: Total number of times the file was opened/unlocked.
- **Detailed Audit Accordion**: Click the views badge to view:
  - **Action**: 👁️ `Viewed` vs 📥 `Downloaded`.
  - **User**: Logged-in user name or `Guest Visitor`.
  - **IP Address**: IP address of the recipient (e.g. `172.26.0.1`).
  - **Date/Time**: Timestamp formatted using system date settings.

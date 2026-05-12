<style>
/* ════════════════════════════════════════
   SMTP & Inbox Settings — Premium Page Styles
   Reusable Partial
════════════════════════════════════════ */
.dash-container { padding: 24px; }

/* ── Banner ── */
.smtp-banner {
    display: flex; align-items: center; gap: 18px;
    background: linear-gradient(135deg, #005757, #163f7a);
    border-radius: 18px; padding: 20px 24px; color: #fff; flex-wrap: wrap;
}
.smtp-banner-icon {
    width: 56px; height: 56px; border-radius: 16px;
    background: rgba(255,255,255,0.15);
    display: flex; align-items: center; justify-content: center;
    font-size: 1.8rem; flex-shrink: 0;
}
.smtp-banner-info { flex: 1; min-width: 0; }
.smtp-banner-title { font-size: 1.05rem; font-weight: 800; }
.smtp-banner-sub   { font-size: 0.78rem; opacity: .80; margin-top: 4px; line-height: 1.5; }
.smtp-status-pill  {
    display: inline-flex; align-items: center; gap: 6px; border: none;
    border-radius: 20px; padding: 7px 16px; font-size: 0.8rem; font-weight: 700; flex-shrink: 0;
    cursor: pointer; transition: all 0.2s;
}
.smtp-status-pill:hover { filter: brightness(1.1); transform: scale(1.02); }
.smtp-configured   { background: rgba(52,168,83,0.2); border: 1px solid rgba(52,168,83,0.4); color: #b7f5c8; }
.smtp-unconfigured { background: rgba(234,67,53,0.2); border: 1px solid rgba(234,67,53,0.4); color: #ffd0cc; }

/* ── Form Card ── */
.smtp-form-card { border-radius: 18px; border: 1px solid #e8eaed; overflow: hidden; background: #fff; }

/* ── Card Header ── */
.smtp-form-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 20px 28px;
    background: linear-gradient(135deg, #005757, #163f7a);
}
.smtp-form-header-title { font-size: 1rem; font-weight: 700; color: #fff; margin: 0; }
.smtp-form-header-sub   { font-size: 0.74rem; color: rgba(255,255,255,.72); margin: 4px 0 0; }

/* ── Body ── */
.smtp-form-body { padding: 28px; background: #f4fbfb; }

/* ── Section title ── */
.smtp-section-title {
    font-size: 0.72rem; font-weight: 700; color: #163f7a;
    text-transform: uppercase; letter-spacing: .07em;
    margin: 24px 0 14px; padding-bottom: 5px;
    border-bottom: 1.5px solid rgba(22, 63, 122,.12);
}

/* ── Field ── */
.smtp-field { display: flex; flex-direction: column; }
.smtp-field label { font-size: 0.78rem; color: #5f6368; margin-bottom: 5px; font-weight: 500; }
.smtp-field .req { color: #ea4335; }

/* ── Input Box ── */
.smtp-input-box {
    display: flex; align-items: center;
    border: 1.5px solid #d1d5db; border-radius: 10px;
    background: #fff; overflow: hidden;
    transition: border-color .15s, box-shadow .15s; height: 44px;
}
.smtp-input-box:focus-within {
    border-color: #163f7a;
    box-shadow: 0 0 0 3px rgba(22, 63, 122,.08);
}
.smtp-icon {
    display: flex; align-items: center; justify-content: center;
    width: 40px; height: 100%; flex-shrink: 0;
    color: #163f7a; font-size: 1.05rem;
    border-right: 1.5px solid #e8eaed; background: #f8fdfd;
}
.smtp-input-box input,
.smtp-input-box select {
    flex: 1; border: none !important; outline: none !important;
    box-shadow: none !important; background: transparent;
    font-size: 0.875rem; color: #202124; padding: 0 12px; height: 100%;
}

/* ── Footer ── */
.smtp-form-footer {
    display: flex; justify-content: flex-end; gap: 8px;
    padding-top: 20px; border-top: 1px solid #e8eaed;
}
.smtp-btn-save {
    display: inline-flex; align-items: center; gap: 6px;
    font-size: 0.85rem; font-weight: 700; padding: 9px 24px; border-radius: 10px;
    border: none; background: #163f7a; color: #fff;
    cursor: pointer; transition: background .15s;
}
.smtp-btn-save:hover { background: #004e4e; }
</style>

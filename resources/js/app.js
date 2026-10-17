import './bootstrap';

const issueSearch = document.querySelector('#issueSearch');
const trendPeriod = document.querySelector('#trendPeriod');
const trendPeriodForm = document.querySelector('#trend-period-form');
const picFilter = document.querySelector('#picFilter');
const notificationTrigger = document.querySelector('#notificationTrigger');
const notificationDropdown = document.querySelector('#notificationDropdown');
const notificationList = notificationDropdown?.querySelector('.notification-list');
const notificationStorageKey = 'hli-hub-read-notifications';
const successModal = document.querySelector('#successModal');
const successModalClose = successModal?.querySelector('.success-modal__close');
const sidebar = document.querySelector('.app-sidebar');
const sidebarToggle = document.querySelector('.sidebar-toggle');
const sidebarStorageKey = 'hli-hub-sidebar-collapsed';
const sidebarAutoCollapseKey = 'hli-hub-sidebar-auto-collapse';
const themeStorageKey = 'hli-hub-theme';
const themeToggle = document.querySelector('#themeToggle');
let sidebarAutoCollapseTimer = null;
const resumeModal = document.querySelector('#resumeModal');
const resumeModalClose = resumeModal?.querySelector('.resume-modal__close');
const resumeModalFooterClose = resumeModal?.querySelector('.resume-modal__footer-close');
const trackingModals = document.querySelectorAll('[data-tracking-modal]');
const documentPreviewModal = document.querySelector('#documentPreviewModal');
const documentPreviewFrame = documentPreviewModal?.querySelector('[data-document-frame]');
const documentPreviewClose = documentPreviewModal?.querySelector('.document-preview-modal__close');
const archiveEditModals = document.querySelectorAll('.legal-product-edit-modal');
document.querySelectorAll('.legal-product-detail').forEach((details) => {
    const card = details.closest('.legal-product-card');
    const syncExpandedCard = () => card?.classList.toggle('legal-product-card--expanded', details.open);

    syncExpandedCard();
    details.addEventListener('toggle', syncExpandedCard);
});
const loginTrigger = document.querySelector('#loginTrigger');
const loginPanel = document.querySelector('#loginPanel');
const loginLanding = loginTrigger?.closest('.login-landing');
const loginTriggerLabel = loginTrigger?.querySelector('[data-login-trigger-label]');
const loginTriggerIcon = loginTrigger?.querySelector('[data-login-trigger-icon]');
let openTrackingModal = null;

const setTheme = (isDark) => {
    document.body.classList.toggle('theme-dark', isDark);
    if (themeToggle) {
        themeToggle.checked = isDark;
    }

    document.querySelectorAll('[data-theme-label]').forEach((label) => {
        label.textContent = isDark ? 'Mode gelap' : 'Mode terang';
    });
    document.querySelectorAll('[data-theme-description]').forEach((description) => {
        description.textContent = isDark
            ? 'Tampilan saat ini menggunakan mode gelap.'
            : 'Tampilan saat ini menggunakan mode terang.';
    });
};

setTheme(window.localStorage.getItem(themeStorageKey) === 'dark');
themeToggle?.addEventListener('change', () => {
    const theme = themeToggle.checked ? 'dark' : 'light';
    window.localStorage.setItem(themeStorageKey, theme);
    setTheme(theme === 'dark');
});

loginTrigger?.addEventListener('click', () => {
    if (!loginPanel) {
        return;
    }

    const isExpanded = loginTrigger.getAttribute('aria-expanded') === 'true';
    loginPanel.hidden = isExpanded;
    loginLanding?.classList.toggle('login-landing--form-open', !isExpanded);
    loginTrigger.setAttribute('aria-expanded', String(!isExpanded));
    if (loginTriggerLabel) {
        loginTriggerLabel.textContent = isExpanded ? 'Login' : 'Tutup formulir';
    }
    if (loginTriggerIcon) {
        loginTriggerIcon.textContent = isExpanded ? '→' : '×';
    }

    if (isExpanded) {
        loginTrigger.focus();
    } else {
        loginPanel.querySelector('#username')?.focus();
    }
});

const getReadNotifications = () => {
    const value = window.localStorage.getItem(notificationStorageKey) || '';

    return new Set(value === '' ? [] : value.split('|'));
};

const updateNotificationReadState = (notification, isRead) => {
    notification.classList.toggle('notification-item--read', isRead);
    notification.classList.toggle('notification-item--unread', !isRead);

    const readState = notification.querySelector('[data-notification-read-state]');
    if (readState) {
        readState.textContent = isRead ? 'Sudah dibaca' : 'Belum dibaca';
    }
};

const updateUnreadBadge = () => {
    const badge = document.querySelector('[data-notification-badge]');
    if (!badge || !notificationList) {
        return;
    }

    const unreadCount = notificationList.querySelectorAll('.notification-item--unread').length;
    badge.textContent = unreadCount > 99 ? '99+' : String(unreadCount);
    badge.hidden = unreadCount === 0;
    notificationTrigger?.setAttribute('aria-label', `Buka notifikasi, ${unreadCount} belum dibaca`);
};

const readNotifications = getReadNotifications();
const markNotificationAsRead = (notification) => {
    if (notification.classList.contains('notification-item--read')) {
        return;
    }

    const notificationId = notification.dataset.notificationId;
    if (!notificationId) {
        return;
    }

    readNotifications.add(notificationId);
    window.localStorage.setItem(notificationStorageKey, [...readNotifications].join('|'));
    updateNotificationReadState(notification, true);
    updateUnreadBadge();
};

if (notificationList) {
    notificationList.querySelectorAll('[data-notification-id]').forEach((notification) => {
        updateNotificationReadState(
            notification,
            readNotifications.has(notification.dataset.notificationId || ''),
        );
    });
    updateUnreadBadge();

    notificationList.addEventListener('click', (event) => {
        const notification = event.target.closest('[data-notification-id]');
        if (!notification) {
            return;
        }

        markNotificationAsRead(notification);
    });
}

const closeNotifications = () => {
    if (!notificationTrigger || !notificationDropdown) {
        return;
    }

    notificationDropdown.hidden = true;
    notificationTrigger.setAttribute('aria-expanded', 'false');
};

const positionNotifications = () => {
    if (!notificationTrigger || !notificationDropdown || notificationDropdown.hidden) {
        return;
    }

    const triggerRect = notificationTrigger.getBoundingClientRect();
    const width = Math.min(390, window.innerWidth - 24);
    const dropdownHeight = notificationDropdown.getBoundingClientRect().height;
    const left = Math.max(12, Math.min(triggerRect.right - width, window.innerWidth - width - 12));
    const belowTop = triggerRect.bottom + 10;
    const top = belowTop + dropdownHeight <= window.innerHeight - 12 || triggerRect.top < dropdownHeight + 22
        ? Math.min(belowTop, window.innerHeight - dropdownHeight - 12)
        : triggerRect.top - dropdownHeight - 10;

    notificationDropdown.style.left = `${left}px`;
    notificationDropdown.style.top = `${Math.max(12, top)}px`;
    notificationDropdown.style.width = `${width}px`;
};

notificationTrigger?.addEventListener('click', () => {
    if (!notificationDropdown) {
        return;
    }

    const isExpanded = notificationTrigger.getAttribute('aria-expanded') === 'true';
    notificationDropdown.hidden = isExpanded;
    notificationTrigger.setAttribute('aria-expanded', String(!isExpanded));
    positionNotifications();
});

notificationDropdown?.querySelector('.notification-dropdown__close')?.addEventListener('click', closeNotifications);
window.addEventListener('resize', positionNotifications);
window.addEventListener('scroll', positionNotifications, { capture: true, passive: true });
document.addEventListener('click', (event) => {
    if (!event.target.closest('.notification-menu')) {
        closeNotifications();
    }
});
document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
        closeNotifications();
    }
});

const setSidebarState = (collapsed) => {
    document.body.classList.toggle('sidebar-collapsed', collapsed);
    sidebarToggle?.setAttribute('aria-expanded', String(!collapsed));
    sidebarToggle?.setAttribute('aria-label', collapsed ? 'Buka sidebar' : 'Tutup sidebar');
    if (sidebarToggle) {
        sidebarToggle.textContent = collapsed ? '›' : '‹';
    }
};

if (sidebar && sidebarToggle) {
    const autoCollapseAt = Number(window.sessionStorage.getItem(sidebarAutoCollapseKey));
    const shouldAutoCollapse = Number.isFinite(autoCollapseAt) && autoCollapseAt > 0 && Date.now() - autoCollapseAt < 10000;
    window.sessionStorage.removeItem(sidebarAutoCollapseKey);

    setSidebarState(shouldAutoCollapse ? false : window.localStorage.getItem(sidebarStorageKey) === 'true');

    if (shouldAutoCollapse) {
        sidebarAutoCollapseTimer = window.setTimeout(() => {
            setSidebarState(true);
            window.localStorage.setItem(sidebarStorageKey, 'true');
            sidebarAutoCollapseTimer = null;
        }, 600);
    }

    sidebarToggle.addEventListener('click', () => {
        if (sidebarAutoCollapseTimer !== null) {
            window.clearTimeout(sidebarAutoCollapseTimer);
            sidebarAutoCollapseTimer = null;
        }

        const collapsed = !document.body.classList.contains('sidebar-collapsed');
        setSidebarState(collapsed);
        window.localStorage.setItem(sidebarStorageKey, String(collapsed));
    });

    sidebar.addEventListener('click', (event) => {
        const link = event.target.closest('a.sidebar-link, a.sidebar-sublink');
        if (!link || event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey || link.target === '_blank') {
            return;
        }

        window.sessionStorage.setItem(sidebarAutoCollapseKey, String(Date.now()));
    });
}

document.querySelectorAll('[data-sidebar-submenu]').forEach((button) => {
    button.addEventListener('click', () => {
        const submenu = document.getElementById(button.getAttribute('aria-controls') || '');
        if (!submenu) {
            return;
        }

        const expanded = button.getAttribute('aria-expanded') === 'true';
        button.setAttribute('aria-expanded', String(!expanded));
        submenu.hidden = expanded;
        button.closest('.sidebar-group')?.classList.toggle('sidebar-group--open', !expanded);
    });
});

issueSearch?.addEventListener('input', (event) => {
    const query = event.target.value.trim().toLowerCase();

    if (query === '' && new URLSearchParams(window.location.search).has('q')) {
        window.location.assign(issueSearch.form.action);

        return;
    }

    document.querySelectorAll('#issuesTable tbody tr').forEach((row) => {
        row.hidden = query !== '' && !row.textContent.toLowerCase().includes(query);
    });
});

trendPeriod?.addEventListener('change', (event) => {
    const selectedPeriod = event.target.value;
    const periodInput = trendPeriodForm?.querySelector('input[name="period"]');

    if (!trendPeriodForm || !periodInput) {
        return;
    }

    periodInput.value = selectedPeriod;
    trendPeriodForm.submit();
});

picFilter?.addEventListener('change', (event) => {
    event.target.form?.submit();
});

const closeSuccessModal = () => {
    successModal?.classList.add('success-modal--hidden');
};

successModalClose?.addEventListener('click', closeSuccessModal);
successModal?.addEventListener('click', (event) => {
    if (event.target === successModal) {
        closeSuccessModal();
    }
});

if (successModal) {
    window.setTimeout(closeSuccessModal, 4000);
}

const closeArchiveEditModal = (modal) => {
    if (modal.open) {
        modal.close();
    }
};

document.querySelectorAll('[data-open-archive-edit]').forEach((button) => {
    button.addEventListener('click', () => {
        const modal = document.getElementById(button.dataset.openArchiveEdit);
        if (!(modal instanceof HTMLDialogElement)) {
            return;
        }

        modal.showModal();
        document.body.classList.add('modal-open');
    });
});

archiveEditModals.forEach((modal) => {
    modal.querySelector('[data-close-archive-edit]')?.addEventListener('click', () => closeArchiveEditModal(modal));
    modal.addEventListener('click', (event) => {
        if (event.target === modal) {
            closeArchiveEditModal(modal);
        }
    });
    modal.addEventListener('close', () => document.body.classList.remove('modal-open'));

    if (modal.hasAttribute('data-reopen')) {
        modal.showModal();
        document.body.classList.add('modal-open');
    }
});

const closeDocumentPreview = () => {
    documentPreviewModal?.classList.add('document-preview-modal--hidden');
    if (documentPreviewFrame) {
        documentPreviewFrame.src = 'about:blank';
    }
    document.body.classList.remove('modal-open');
};

document.querySelectorAll('[data-document-preview]').forEach((button) => {
    button.addEventListener('click', () => {
        if (!documentPreviewModal || !documentPreviewFrame) {
            return;
        }

        documentPreviewModal.querySelector('[data-document-title]').textContent = button.dataset.documentTitle || '';
        documentPreviewModal.querySelector('[data-document-name]').textContent = button.dataset.documentName || '';
        documentPreviewModal.querySelector('[data-document-download]').href = button.dataset.downloadUrl || '#';
        documentPreviewFrame.src = button.dataset.previewUrl || 'about:blank';
        documentPreviewModal.classList.remove('document-preview-modal--hidden');
        document.body.classList.add('modal-open');
    });
});

documentPreviewClose?.addEventListener('click', closeDocumentPreview);
documentPreviewModal?.addEventListener('click', (event) => {
    if (event.target === documentPreviewModal) {
        closeDocumentPreview();
    }
});

const closeResumeModal = () => {
    resumeModal?.classList.add('resume-modal--hidden');
    document.body.classList.remove('modal-open');
};

const openResumeModal = (button) => {
        if (!resumeModal) {
            return;
        }

        let resume;
        try {
            const rawResume = button.getAttribute('data-resume') || '{}';
            const decodedResume = document.createElement('textarea');
            decodedResume.innerHTML = rawResume;
            resume = JSON.parse(decodedResume.value);
        } catch {
            console.error('Resume kegiatan tidak dapat dibaca.');
            return;
        }

        resumeModal.querySelectorAll('[data-resume-field]').forEach((element) => {
            const field = element.dataset.resumeField;
            if (field === 'progress-bar') {
                element.style.width = `${Math.max(0, Math.min(100, Number(resume.progres) || 0))}%`;
                return;
            }

            element.textContent = field === 'progres'
                ? `${resume[field] ?? 0}%`
                : (resume[field] || '-');
        });
        const statusBox = resumeModal.querySelector('.resume-box--status');
        const normalizedStatus = String(resume.status || '').trim().toUpperCase();
        statusBox?.classList.remove('resume-box--status-done', 'resume-box--status-progress', 'resume-box--status-pending');
        statusBox?.classList.add(
            ['SELESAI', 'DONE', 'COMPLETED'].includes(normalizedStatus)
                ? 'resume-box--status-done'
                : (['ON PROGRESS', 'PROGRESS', 'IN PROGRESS'].includes(normalizedStatus)
                    ? 'resume-box--status-progress'
                    : 'resume-box--status-pending'),
        );
        resumeModal.classList.remove('resume-modal--hidden');
        document.body.classList.add('modal-open');
};

document.querySelectorAll('.resume-button').forEach((button) => {
    button.addEventListener('click', () => openResumeModal(button));
});

document.querySelectorAll('form[data-confirm]').forEach((form) => {
    form.addEventListener('submit', (event) => {
        if (!window.confirm(form.dataset.confirm)) {
            event.preventDefault();
        }
    });
});

const closeTrackingModal = (modal) => {
    modal?.classList.add('tracking-modal--hidden');
    if (openTrackingModal === modal) {
        openTrackingModal = null;
        document.body.classList.remove('modal-open');
    }
};

const showTrackingModal = (modal) => {
    if (!modal) {
        return;
    }
    modal.classList.remove('tracking-modal--hidden');
    openTrackingModal = modal;
    document.body.classList.add('modal-open');
};

document.querySelectorAll('[data-tracking-open]').forEach((button) => {
    button.addEventListener('click', () => {
        showTrackingModal(document.getElementById(button.dataset.trackingOpen));
    });
});

trackingModals.forEach((modal) => {
    modal.querySelector('.tracking-modal__close')?.addEventListener('click', () => closeTrackingModal(modal));
    modal.addEventListener('click', (event) => {
        if (event.target === modal) {
            closeTrackingModal(modal);
        }
    });
});

const trackingIssueToReopen = document.body.dataset.openTracking;
if (trackingIssueToReopen) {
    showTrackingModal(document.getElementById(`trackingModal${trackingIssueToReopen}`));
}

const stakeholderCreate = document.querySelector('.stakeholder-create');
const closeStakeholderCreate = () => {
    if (stakeholderCreate?.open) {
        stakeholderCreate.open = false;
        stakeholderCreate.querySelector('summary')?.focus();
    }
};
stakeholderCreate?.querySelectorAll('.stakeholder-create__backdrop, .stakeholder-create__close').forEach((button) => {
    button.addEventListener('click', closeStakeholderCreate);
});

const stakeholderAddMember = document.querySelector('.stakeholder-add-member');
const closeStakeholderMember = () => {
    if (stakeholderAddMember?.open) {
        stakeholderAddMember.open = false;
        stakeholderAddMember.querySelector('summary')?.focus();
    }
};
stakeholderAddMember?.querySelectorAll('.stakeholder-member__backdrop, .stakeholder-member__close').forEach((button) => {
    button.addEventListener('click', closeStakeholderMember);
});

resumeModalClose?.addEventListener('click', closeResumeModal);
resumeModalFooterClose?.addEventListener('click', closeResumeModal);
resumeModal?.addEventListener('click', (event) => {
    if (event.target === resumeModal) {
        closeResumeModal();
    }
});
document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && resumeModal && !resumeModal.classList.contains('resume-modal--hidden')) {
        closeResumeModal();
    }
    if (event.key === 'Escape' && openTrackingModal) {
        closeTrackingModal(openTrackingModal);
    }
    if (event.key === 'Escape' && documentPreviewModal && !documentPreviewModal.classList.contains('document-preview-modal--hidden')) {
        closeDocumentPreview();
    }
    if (event.key === 'Escape' && stakeholderCreate?.open) {
        closeStakeholderCreate();
    }
    if (event.key === 'Escape' && stakeholderAddMember?.open) {
        closeStakeholderMember();
    }
});

window.currentMediaField = null;
let isMultipleMode = false;
function openMediaLibrary(fieldName) {
    window.currentMediaField = fieldName;

    const modal = document.getElementById("media-modal");
    if (!modal) {
        console.error("Modal element not found!");
        return;
    }

    modal.classList.remove("hidden");
    modal.classList.add("flex");
}

function closeMediaModal() {
    const modal = document.getElementById("media-modal");
    modal.classList.add("hidden");
    modal.classList.remove("flex");
}

function selectMedia(id, url) {
    if (!window.currentMediaField) {
        console.error("currentMediaField not set.");
        return;
    }

    const picker = document.querySelector(`[data-field="${window.currentMediaField}"]`);
    if (!picker) {
        console.error("Picker element not found!", window.currentMediaField);
        return;
    }

    // ست کردن hidden input
    picker.querySelector('input[type="hidden"]').value = id;

    // ست کردن preview
    picker.querySelector('.media-preview').src = url;

    closeMediaModal();
}

function openMediaManager(targetFieldId, multiple = false) {
    currentTargetField = targetFieldId;
    isMultipleMode = multiple;

    // مودال را باز کن
    document.getElementById('media-manager-modal').classList.remove('hidden');

    // لود فایل‌ها از سرور
    loadMediaList();
}

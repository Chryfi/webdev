class UploadElement {
    #element;
    #uploadDefaultView;
    #errorOverlay;
    #errorMessageElement;
    #fileListElement;
    #uploadHoverOverlay;
    #loadingOverlay;
    #validFileTypes;
    /**
     * This callback is executed after {@link fileResult} and {@link file} have been set
     * with the newly uploaded element.
     * The callback receives "this" instance as argument. When the callback returns false, the uploading will be aborted and
     * the variables {@link fileResult} and {@link file} will be reset to the previous uploaded element.
     * The callback is allowed to return a {@link Promise}.
     */
    onLoad;
    fileResult;
    /**
     * @type File
     */
    file;
    /**
     * For Form POST
     * the file will be encoded as base64 URI and pasted into the input value
     * @type {HTMLInputElement}
     */
    fileInputCache;

    constructor(validFileTypes) {
        this.#createElement();
        this.initEvents();
        this.#validFileTypes = validFileTypes;
    }

    getElement() {
        return this.#element;
    }

    #createElement() {
        this.#element = document.createElement("div");
        this.#element.classList.add("drop-upload", "row");
        this.#element.id = "image-upload";

        this.#createDefaultView();
        this.#createErrorOverlay();
        this.#createHoverOverlay();
        this.#createLoadingOverlay();

        this.#element.appendChild(this.#uploadDefaultView);
        this.#element.appendChild(this.#errorOverlay);
        this.#element.appendChild(this.#uploadHoverOverlay);
        this.#element.appendChild(this.#loadingOverlay);
    }

    #createHoverOverlay() {
        this.#uploadHoverOverlay = document.createElement("div");
        this.#uploadHoverOverlay.classList.add("upload-hover-overlay", "row", "justify-content-center", "align-items-center");
        this.#uploadHoverOverlay.innerHTML = `
        <div class="col-5">
            <h3 class="text-center"><i class="fas fa-upload fa-xl"></i> Drop your file</h3>
        </div>`;
    }

    #createLoadingOverlay() {
        this.#loadingOverlay = document.createElement("div");
        this.#loadingOverlay.classList.add("upload-progressbar", "row", "justify-content-center", "align-items-center");
        this.#loadingOverlay.innerHTML = `
        <div class="col-5">
            <h3 class="text-center">Loading...</h3>
        </div>`;
    }

    #createErrorOverlay() {
        this.#errorOverlay = document.createElement("div");
        this.#errorOverlay.classList.add("upload-error-overlay");

        let divElement = document.createElement('div');
        divElement.classList.add('justify-content-center');
        this.#errorOverlay.appendChild(divElement);

        let iElement = document.createElement('i');
        iElement.classList.add('fa-solid', 'fa-circle-exclamation', 'fa-xl');

        divElement.appendChild(iElement);

        this.#errorMessageElement = document.createElement('p');
        this.#errorMessageElement.classList.add('display-inline-block');
        this.#errorMessageElement.id = 'error-text';
        this.#errorMessageElement.textContent = 'Error';

        divElement.appendChild(this.#errorMessageElement);
    }

    #createDefaultView() {
        this.#uploadDefaultView = document.createElement("div");
        this.#uploadDefaultView.classList.add("upload-default", "row", "justify-content-center", "align-items-center");

        let uploadImageCol = document.createElement("div");
        uploadImageCol.classList.add("col");
        uploadImageCol.innerHTML = `
        <h3><i class="fa-solid fa-upload fa-xl"></i>
        <a href="#" class="link-info">Upload image</a></h3>`;
        this.#uploadDefaultView.appendChild(uploadImageCol);

        let fileListContainer = document.createElement("div");
        fileListContainer.classList.add("col", "uploaded-files-list");
        this.#uploadDefaultView.appendChild(fileListContainer);

        let icon = document.createElement("i");
        icon.classList.add("fa-solid", "fa-file", "fa-xl");
        fileListContainer.appendChild(icon);

        this.#fileListElement = document.createElement("p");
        this.#fileListElement.classList.add("display-inline-block");
        this.#fileListElement.id = "uploaded-file-name";
        fileListContainer.appendChild(this.#fileListElement);
    }

    initEvents() {
        this.#element.addEventListener("click", (e) => this.onClick(e));
        this.#element.addEventListener("drop", (e) => this.onDrop(e));
        this.#element.addEventListener("dragover", (e) => e.preventDefault());
        this.#element.addEventListener("dragenter", (e) => this.onDragEnter(e));
        this.#element.addEventListener("dragleave", (e) => this.onDragLeave(e));
    }

    onDragLeave(e) {
        e.preventDefault();
        e.stopPropagation();

        this.#element.classList.remove("hover");
    }

    onDragEnter(e) {
        e.preventDefault();
        e.stopPropagation();

        this.#element.classList.add("hover");
    }

    onClick(e) {
        e.preventDefault();

        /* create a fake input for clicking upload */
        let input = document.createElement('input');
        input.type = 'file';

        input.onchange = e => {
            this.upload(input.files);
        };

        input.click();
    }

    onDrop(e) {
        e.preventDefault();
        e.stopPropagation();

        let items = e.dataTransfer.items;

        if (items && items.length > 0) {
            if (items[0].kind === 'file') {
                this.upload(e.dataTransfer.files);
            }
        }

        this.#element.classList.remove("hover");
    }

    /**
     *
     * @param {FileList} fileList
     */
    upload(fileList) {
        let file = fileList[0];

        if (file == null) {
            return;
        }

        if (this.#validFileTypes != null && Array.isArray(this.#validFileTypes)
            && this.#validFileTypes.length !== 0 && !this.#validFileTypes.includes(file.type)) {
            this.displayError("Kein gültiges Dateiformat.");
            return;
        }

        this.#element.classList.remove("error");
        this.#element.classList.add("loading");

        const reader = new FileReader();

        reader.onload = () => {
            this.uploadURI(reader.result, file);
        }

        reader.onerror = () => {
            this.#element.classList.remove("loading");
            this.displayError("Fehler beim lesen der Datei.");
        }

        reader.readAsDataURL(file);
    }

    /**
     *
     * @param {string} uri base64 encoded
     * @param {File} file
     */
    async uploadURI(uri, file) {
        let oldFileResult = this.fileResult;
        let oldFile = this.file;

        this.fileResult = uri;
        this.file = file;
        let success = true;
        if (this.onLoad != null) {
            let promise = await this.onLoad(this);

            if (promise === false) success = false;
        }

        if (success) {
            if (this.fileInputCache != null) {
                this.fileInputCache.value = this.fileResult;
            }

            this.#fileListElement.textContent = "Uploaded " + this.file.name;
            this.#element.classList.add("uploaded");
            this.#element.classList.remove("error");
        } else {
            this.fileResult = oldFileResult;
            this.file = oldFile;
        }

        this.#element.classList.remove("loading");
    }

    displayError(message) {
        this.#element.classList.add("error");
        this.#errorMessageElement.textContent = message;
        let time = (message != null) ? message.length * 85 : 5000;
        setTimeout(() => {
            this.#element.classList.remove("error");
            this.#errorMessageElement.textContent = "";
        }, time);
    }
}
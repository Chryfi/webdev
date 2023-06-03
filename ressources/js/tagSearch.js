/**
 * Every tag item picked by the user has the id as following in the wrapping element:
 * "tag-search-result-" + tagName , see {@link #createTagResultElement}. This is important to prevent duplicate entries.
 * Every created tag element that is picked by the user has a hidden input with the name "tags[]" and the value tagName.
 * This is needed for easy PHP POST stuff.
 *
 * When this class is instantiated it will look through the {@link tagList} for children
 * (assumes direct children of tagList are picked tag items) and adds an EventListener for removing that tag item.
 * This is useful when PHP outputs the tags when posting a form.
 */
class TagSearch {
    /**
     * The element that displays the results
     * from which you can pick one tag
     */
    #tagContainer;
    #tagSearchList;
    /**
     * The div where the tags should
     * be pasted into after selecting one
     */
    tagList;
    tagInput;

    constructor(tagInput, tagList) {
        this.tagInput = tagInput;
        this.tagList = tagList;
        this.#createTagContainer();

        this.initEvents();
    }

    initEvents() {
        this.tagInput.addEventListener("input", () => this.onSearch(this.tagInput.value));
        this.tagInput.addEventListener("keydown", (event) => {
            if (event.keyCode === 13) {
                event.preventDefault();
                this.onEnter(this.tagInput.value);
            }
        });

        /* when there are already tag elements in the tag list. Might be from PHP output */
        document.addEventListener("DOMContentLoaded", () => {
            if (this.tagList.children.length !== 0) {
                this.#attachEventsTaglistItems();
            }
        });
    }

    #attachEventsTaglistItems() {
        for (let i = 0; i < this.tagList.children.length; i++) {
            let element = this.tagList.children[i];

            let removeTag = element.querySelector(".tag-remove-button");
            if (removeTag != null && element.id != null) {
                removeTag.addEventListener("click", () => {
                    this.removeTagResult(element.id);
                });
            }
        }
    }

    #createTagContainer() {
        this.#tagContainer = document.createElement("div");
        this.#tagContainer.classList.add("tag-search-results-container");

        this.#tagSearchList = document.createElement("div");
        this.#tagSearchList.classList.add("row", "tag-list");
        this.#tagContainer.append(this.#tagSearchList);
    }

    /**
     * For the tag list. These are the selected tags.
     * @param tagName
     * @returns {HTMLElement}
     */
    #createTagResultElement(tagName) {
        let tagItem = document.createElement("div");
        tagItem.classList.add("tag-item", "col-auto");
        tagItem.id = "tag-search-result-" + tagName;

        const divElement = document.createElement("div");
        divElement.classList.add("row", "tag-element-row", "align-items-center");
        tagItem.appendChild(divElement);

        const pElement = document.createElement("p");
        pElement.classList.add("col-auto");
        pElement.textContent = tagName;
        divElement.appendChild(pElement);

        const iElement = document.createElement("i");
        iElement.classList.add("col-auto", "fa-solid", "fa-xmark", "color-remove", "tag-remove-button");
        iElement.onclick = e => {
            this.removeTagResult(tagItem.id);
        };
        divElement.appendChild(iElement);

        /* input for PHP POST */
        const inputElement = document.createElement("input");
        inputElement.setAttribute("type", "text");
        inputElement.setAttribute("name", "tags[]");
        inputElement.setAttribute("value", tagName);
        inputElement.setAttribute("hidden", "");
        divElement.appendChild(inputElement);

        return tagItem;
    }

    /**
     * For the list of possible search candidates from which the user
     * will select one.
     * @param tagName
     * @param articleCount
     * @returns {HTMLElement}
     */
    #createTagSearchElement(tagName, articleCount) {
        let tagItem = document.createElement("p");
        tagItem.classList.add("col-auto", "tag-item");
        tagItem.textContent = tagName + " - " + articleCount;
        tagItem.style.cursor = "pointer";

        tagItem.addEventListener("click", () => {
            this.addTagResult(tagName);
            this.endSearch();
        });

        return tagItem;
    }

    /**
     * Update states and visibilities for the search
     */
    beginSearch() {
        this.#tagSearchList.innerHTML = "";
        this.tagInput.parentElement.style.position = "relative";
        this.tagInput.insertAdjacentElement("afterend", this.#tagContainer);
    }

    /**
     * Remove elements from the DOM and clear input value
     */
    endSearch() {
        this.#tagSearchList.innerHTML = "";
        this.#tagContainer.remove();
        this.tagInput.value = "";
        this.tagInput.parentElement.style.position = "";
    }

    onEnter(tagName) {
        this.addTagResult(tagName);
        this.endSearch();
    }

    onSearch(search) {
        if (search === "") {
            this.endSearch();
            return;
        }

        this.#fetchTags(search).then((result) => {
            this.beginSearch();
            for (let tagName in result) {
                let number = result[tagName];

                this.#tagSearchList.append(this.#createTagSearchElement(tagName, number));
            }
        });
    }

    addTagResult(tagName) {
        tagName = tagName.toLowerCase();
        let tagElement = this.#createTagResultElement(tagName);

        /* prevent double tags by using id as easy reference */
        if (!document.getElementById(tagElement.id)) {
            this.tagList.append(tagElement);
        }
    }

    removeTagResult(id) {
        let tagElement = document.getElementById(id);
        if (tagElement != null) {
            tagElement.remove();
        }
    }

    isTagListEmpty() {
        return this.tagList.children.length === 0;
    }

    async #fetchTags(searchName) {
        let searchData = new FormData();
        searchData.append("tag-search", searchName);

        let result = await fetch("/src/application/tagSearch.php", {
            method: "POST",
            body: searchData,
        });

        return await result.json();
    }
}
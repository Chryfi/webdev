/**
 * The button that controls the dropdown needs to have the class "dropdown-button"
 * and a data attribute "dropdown-target" with the id of the content container that should be expanded / collapsed.
 * 
 * The content container that will be expanded / collapsed needs to have the class "dropdown-collapse"
 * 
 * In the collapsed state both button and content container will get the class "collapsed".
 * While animating the content container into either expanded or collapsed state,
 * the "collapsed" class will be removed and "collapsing" class will be assigned only for the time period of animation.
 *
*/

document.addEventListener("DOMContentLoaded", e => {
    let dropDownButtons = document.getElementsByClassName("dropdown-button");
    for (let i = 0; i < dropDownButtons.length; i++) {
        let element = dropDownButtons.item(i);

        if (!("dropdownTarget" in element.dataset) || document.getElementById(element.dataset["dropdownTarget"]) == null) {
            continue;
        }

        let target = document.getElementById(element.dataset["dropdownTarget"]);

        let btn = new DropdownButton(element, target);
        btn.init();
    }
});


/**
 * This class allows for a dropdown button to collapse/expand another element.
 * The expanding and collapsing timers are stored, to allow interrupting the respective animation and changing it.
 * This allows for a fluent animation even when clicking on the dropdown button while it is already animating.
 */
class DropdownButton {
    #expandingTimer;
    #collapsingTimer;
    #element;
    #target;
    #duration;

    constructor(element, target, duration = 200) {
        this.#element = element;
        this.#target = target;
        this.#duration = duration;
    }

    /**
     * Registers the events. Should be called always after creating the instance.
     */
    init() {
        this.#element.addEventListener("click", e => this.onClick(e));
    }

    onClick(e) {
        if (this.#element.classList.contains("collapsed")) {
            this.#element.classList.remove("collapsed");
            this.#target.classList.remove("collapsed");

            
            if (this.#collapsingTimer != null) {
                this.#target.style.height = this.#target.offsetHeight + "px";
                clearTimeout(this.#collapsingTimer);
                this.#collapsingTimer = null;
            }

            this.#target.classList.add("collapsing");
            let height = this.#calculateHeight(this.#target);
            
            /* 
             * Give the DOM time to update
             * otherwise the css "collapsing" class will not recognise the height change for the transition
             */
            setTimeout(() => {
                this.#target.style.height = height + "px";
            }, 0);

            this.#expandingTimer = setTimeout(() => {
                this.#target.classList.remove("collapsing");
                this.#target.style.height = "";
                this.#expandingTimer = null;
            }, this.#duration);
        } else {
            if (this.#expandingTimer != null) {
                this.#target.style.height = this.#target.offsetHeight + "px";
                clearTimeout(this.#expandingTimer);
                this.#expandingTimer = null;
            } else {
                /* Set the height explicitly so the css transition can work */
                let height = this.#calculateHeight(this.#target);
                this.#target.style.height = height + "px";
            }

            this.#element.classList.add("collapsed");

            setTimeout(() => {
                this.#target.classList.add("collapsing");
                this.#target.style.height = "0px";
            }, 0);
            
            this.#collapsingTimer = setTimeout(() => {
                this.#target.classList.remove("collapsing");
                this.#target.classList.add("collapsed");
                this.#target.style.height = "";
                this.#collapsingTimer = null;
            }, this.#duration);
        }
    }

    #calculateHeight(target) {
        /*
        * Replace the target element with a clone to calculate the goal height
        */
        let clone = target.cloneNode(true);
        clone.id = clone.id !== "" ? clone.id : Math.random() * 99999999;
        clone.classList.remove("collapsing");
        clone.style.height = "";
        /*
        * overflow hidden is needed so the height of the element can be calculated properly.
        * Otherwise things like margins of child elements will overflow
        * and not influence the parent's height
        */
        clone.style.overflow = "hidden";
        target.parentElement.replaceChild(clone, target);
        let height = document.getElementById(clone.id).offsetHeight;
        clone.parentElement.replaceChild(target, clone);

        return height;
    }
}
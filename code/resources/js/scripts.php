<script>
    /**
     * Calculates the mean and standard deviation of some numbers.
     *
     * @param {number[]} numbers
     * @return {number[]} mean and std
     */
    function getMoments(numbers) {
        const mean = numbers.reduce((accumulator, number) => accumulator + number, 0) / numbers.length;
        const std = Math.sqrt(numbers.reduce((accumulator, number) => accumulator + Math.pow(number - mean, 2), 0.001) / numbers.length);
        return [mean, std];
    }

    /**
     * Get Z values. Here for more info: https://en.wikipedia.org/wiki/Standard_score
     *
     * @param {number[]} numbers
     * @return {number[]} z values
     */
    function getZValues(numbers) {
        const moments = getMoments(numbers);
        if (displayMetrics) console.log(`Moments: ${moments}`);
        return numbers.map(number => (number - moments[0]) / moments[1]);
    }

    /**
     * Find elements that have the embedded strings, 2 elements for each string. If strings is empty, then returns an empty array.
     *
     * @param {string[]} strings
     * @return {Element[]}
     */
    function elementsFromStrings(strings) {
        let elements = [];
        for (let i = 0; i < strings.length; i++) {
            let elems = findDistinctElems(strings[i]);
            let samplesTaken = 0;
            for (let j = 0; j < elems.length; j++) {
                if (elems[j].tagName.toLowerCase() !== "script") {
                    elements.push(elems[j]);
                    samplesTaken++;
                }
                if (samplesTaken >= 2) break;
            }
        }
        return elements;
    }

    /**
     * Draws a bounding box given elements. Will filter out outliers at 2 sigmas and above. Returns the center y coordinate
     * of the bounding box when operation is successful.
     *
     * This operation may fail when:
     * - Standard deviation after removing outliers larger than limit (300, which is around a full page (90% of page has 290 std))
     * - There are no elements left after removing outliers
     *
     * @param {Element[]} elements
     * @param {jQuery} boundingBox
     * @return {number} 0 if unsuccessful, bounding box center y coordinate if successful
     */
    function boundingBoxOfElements(elements, boundingBox) {
        if (elements.length === 0) return 0;
        const sigma = 2, maxStd = 300;
        // get rectangles, filter out elements with 0 top and 0 left, because they're probably script tags. We do this to avoid messing up the distribution
        /** @type {DOMRect[]} rects */ const rects = elements.map(element => element.getBoundingClientRect()).filter(rect => (rect.top !== 0 || rect.left !== 0));
        // calculate z values for y midpoint
        const zValues = getZValues(rects.map(location => (location.top + location.bottom) / 2));
        if (displayMetrics) {
            console.log(`Elements:`, elements);
            console.log(`Z values:`, zValues);
        }
        /** @type {DOMRect[]} filteredRects */ let filteredRects = []; // filter outliers with z value more than 2 sigmas
        for (let i = 0; i < rects.length; i++) if (Math.abs(zValues[i]) < sigma) filteredRects.push(rects[i]);
        // check whether spread is still too wide:
        if (getMoments(filteredRects.map(location => (location.top + location.bottom) / 2))[1] > maxStd) return 0;
        if (filteredRects.length === 0) return 0;
        // calculate bounding box and draw it
        const minTop = filteredRects.reduce((accumulator, value) => Math.min(accumulator, value.top), filteredRects[0].top);
        const maxBottom = filteredRects.reduce((accumulator, value) => Math.max(accumulator, value.bottom), filteredRects[0].bottom);
        const minLeft = filteredRects.reduce((accumulator, value) => Math.min(accumulator, value.left), filteredRects[0].left);
        const maxRight = filteredRects.reduce((accumulator, value) => Math.max(accumulator, value.right), filteredRects[0].right);
        boundingBox.css("left", minLeft).css("top", minTop).css("width", Math.min(maxRight - minLeft, 0.7 * window.innerWidth - minLeft)).css("height", maxBottom - minTop);
        return (minTop + maxBottom) / 2;
    }

    /**
     * Filter out all elements that are the parent of some other element.
     *
     * @param {Element[]} elems
     * @return {Element[]}
     */
    function filterParents(elems) {
        let mark = new Array(elems.length).fill(true);
        for (let i = 0; i < elems.length; i++) // O(n^2), but much lower than that (< O(n^2/10)) in reality, because most of the options are pruned right away
            if (mark[i])
                for (let j = i + 1; j < elems.length; j++)
                    if (mark[i] && mark[j]) {
                        if (elems[i].contains(elems[j])) mark[i] = false;
                        if (elems[j].contains(elems[i])) mark[j] = false;
                        if (elems[i] === elems[j]) mark[i] = false; // pruning i index, to clear out O(n) right away
                    }
        let distinctElems = []
        for (let i = 0; i < elems.length; i++) if (mark[i]) distinctElems.push(elems[i]);
        return distinctElems;
    }

    /**
     * Given a string, find elements that are distinct from each other that contains the string.
     *
     * @param {string} str
     * @return {Element[]}
     */
    function findDistinctElems(str) {
        // noinspection RegExpRedundantEscape
        return filterParents(gui.page.contents().find(":contains('" + str.replace(/[#;&,\.\+\*~':"!\^\$\[\]\(\)=>|\/\\]/g, '\\$&') + "')"));
    }

    /**
     * Extracts texts from a node
     *
     * @param {Node} node
     * @returns {string[]}
     */
    function extractText(node) {
        let texts = [];
        // excluding MathJax-related texts
        // noinspection JSUnresolvedVariable
        if (node.className) if (node.className.indexOf("mjx") !== -1) return [];
        // noinspection JSUnresolvedVariable
        if (node.tagName) if (node.tagName.toLowerCase().indexOf("script") !== -1) return [];
        if (node.childNodes.length === 0) return [node.textContent];
        for (let i = 0; i < node.childNodes.length; i++) texts = texts.concat(extractText(node.childNodes[i]));
        return texts.filter(text => text !== "\n");
    }

    /**
     * Extracts texts from the current selection. If nothing is selected, returns empty array.
     *
     * @returns {string[]|null} Returns null if cross-origin error appears
     */
    function selectionStrings() {
        try {
            const sel = gui.pageContentWindow.getSelection();
            // noinspection EqualityComparisonWithCoercionJS
            if (sel == false) return [];
            return extractText(sel.getRangeAt(0).cloneContents()).filter(text => text !== "").filter(text => text.length > 5);
        } catch (e) { // Cross origin error, raised from .getSelection() above
            return null;
        }
    }

    /**
     * Represents a highlighted area, with comments and whatnot. This class retains lots of controls of itself.
     */
    class Highlight {
        /**
         * Constructs a highlight section.
         *
         * @param {Highlights} parent
         * @param {Element[]} elements
         * @param {number} id
         * @param {string[]} selectedStrings strings that identifies the highlight
         * @param {string} comment User's comment
         */
        constructor(parent, elements, id, selectedStrings, comment) {
            /** @type {Highlights} this.parent */ this.parent = parent;
            /** @type {Element[]} this.elements */ this.elements = elements;
            /** @type {number} this.id */ this.id = id;
            /** @type {string[]} this.selectedStrings */ this.selectedStrings = selectedStrings;
            /** @type {string} this.comment */ this.comment = comment;
            /** @type {boolean} this.active */ this.active = true;
            /** @type {boolean} this.normalDisplayMode */ this.normalDisplayMode = true;
            /** @type {number} this.serverHighlightId */ this.serverHighlightId = -1;
            /** @type {number} this.reconciliationCount */ this.reconciliationCount = 20; // twice a second, for 10 seconds

            gui.panel.append(`<div class="boundingBox" id="bb${this.id}"></div>`);
            /** @type {jQuery} this.boundingBoxReference */ this.boundingBoxReference = $(`#bb${this.id}`);

            gui.panel.append(`<div class="contentBox w3-border w3-round-large w3-card" id="cb${this.id}">
<label>Comment</label>
<textarea class="w3-input w3-border w3-round comment" id="comment${this.id}">${this.comment}</textarea>
<button class="updateComment w3-btn w3-indigo w3-round" onclick="highlights.updateHighlightComment(${this.id})">Update</button>
<button class="w3-btn w3-light-green w3-round" onclick="highlights.delete(${this.id})">Delete</button>
</div>`);
            /** @type {jQuery} this.contentBoxReference */ this.contentBoxReference = $(`#cb${this.id}`);
            /** @type {jQuery} this.commentReference */ this.commentReference = $(`#comment${this.id}`);

            // make text areas auto resizeable
            $(`#cb${this.id} textarea.comment`).each(function () {
                this.setAttribute('style', 'height:' + (this.scrollHeight) + 'px;overflow-y:hidden;resize:none;');
            }).on('input', function () {
                this.style.height = 'auto';
                this.style.height = (this.scrollHeight) + 'px';
            });
        }

        /**
         * When the page is starting up, the embedded page might take a long time to load. So at that point, we can't detect
         * pieces of texts right away, because there aren't any
         */
        startReconciliation() {
            this.reconciliationCount--;
            if (this.reconciliationCount > 0) {
                if (!this.active) {
                    // try to make active
                    this.elements = elementsFromStrings(this.selectedStrings);
                    const y = boundingBoxOfElements(this.elements, this.boundingBoxReference);
                    if (y !== 0) {
                        this.contentBoxReference.css("top", y - this.contentBoxReference.height() / 2);
                        this.active = true;
                        this.parent.reconcilingCount--
                        if (this.parent.reconcilingCount === 0) toast.display("Done");
                    } else {
                        const highlightId = this.id;
                        setTimeout(() => {
                            highlights.get(highlightId).startReconciliation()
                        }, 500);
                    }
                }
            } else {
                if (!this.active) {
                    // notify that it can't be moved
                    this.switchToUnknown();
                    this.parent.updateUnknownAmount();
                    this.parent.reconcilingCount--
                    if (this.parent.reconcilingCount === 0) toast.display("Some highlights are broken and are moved into the unknown section", 5000);
                }
            }
        }

        setServerHighlightId(serverHighlightId) {
            this.serverHighlightId = serverHighlightId;
        }

        /**
         * Updates the current display mode.
         *
         * @param normalDisplayMode Whether we are displaying highlights normally or not
         * @param display Whether to display it. This special case is for initializing the highlights from the database
         */
        updateDisplayMode(normalDisplayMode, display = true) {
            this.normalDisplayMode = normalDisplayMode;
            if (this.normalDisplayMode) {
                if (this.active) {
                    this.contentBoxReference.css("display", "block")
                    this.boundingBoxReference.css("display", "block")
                } else {
                    this.contentBoxReference.css("display", "none")
                    this.boundingBoxReference.css("display", "none")
                }
            } else {
                if (this.active) {
                    this.contentBoxReference.css("display", "none")
                    this.boundingBoxReference.css("display", "none")
                } else {
                    gui.unknowns.append(`<div class="unknownBox w3-border w3-round-large w3-card" id="ub${this.id}">
<label>Selected text</label>
<textarea class="w3-input w3-border w3-round selectedStrings" disabled>${this.selectedStrings.map(text => text.trim()).join(" ")}</textarea>
<label>Comment</label>
<textarea class="w3-input w3-border w3-round comment" id="comment${this.id}">${this.comment}</textarea>
<button class="w3-btn w3-indigo w3-round" onclick="highlights.updateHighlightComment(${this.id})">Update</button>
<button class="w3-btn w3-light-green w3-round" onclick="highlights.delete(${this.id})">Delete</button>
</div>`)
                    // make the text areas auto resizeable
                    $(`#ub${this.id} textarea.selectedStrings`).each(function () {
                        this.setAttribute('style', 'height:' + (this.scrollHeight) + 'px;overflow-y:hidden;resize:none;margin-bottom:6px;');
                    });

                    $(`#ub${this.id} textarea.comment`).each(function () {
                        this.setAttribute('style', 'height:' + (this.scrollHeight) + 'px;overflow-y:hidden;resize:none;');
                    }).on('input', function () {
                        this.style.height = 'auto';
                        this.style.height = (this.scrollHeight) + 'px';
                    });
                    /** @type {jQuery} this.unknownBoxReference */ this.unknownBoxReference = $(`#ub${this.id}`);
                    /** @type {jQuery} this.commentReference */ this.commentReference = $(`#comment${this.id}`);
                }
            }
            if (display) this.display();
        }

        /**
         * Convenience function for testing. Switches this element to being unknown (active == false) and do other representation
         * preserving operations.
         */
        switchToUnknown() {
            this.contentBoxReference.css("display", "none");
            this.boundingBoxReference.css("display", "none");
            this.active = false;
            this.parent.updateUnknownAmount();
        }

        /**
         * Updates the position of this highlight, used when the user is scrolling.
         */
        display() {
            if (this.normalDisplayMode)
                if (this.active) {
                    const y = boundingBoxOfElements(this.elements, this.boundingBoxReference);
                    if (y === 0) this.switchToUnknown();
                    else this.contentBoxReference.css("top", y - this.contentBoxReference.height() / 2);
                }
        }

        /**
         * Deletes this highlight.
         */
        delete(contactServer = true) {
            this.contentBoxReference.remove();
            this.boundingBoxReference.remove();
            if (this.unknownBoxReference !== undefined) this.unknownBoxReference.remove();
            if (contactServer) {
                $.ajax({
                    url: "<?php echo DOMAIN_CONTROLLER . "/deleteHighlight"; ?>",
                    type: "POST",
                    data: {
                        highlightId: this.serverHighlightId
                    },
                    error: () => toast.display("Can't connect to server to delete. Please check your internet connection.")
                });
            }
        }
    }

    /**
     * Mainly convenience class to manipulate lots of "Highlight"s at once. Also does some padding operations where necessary.
     */
    class Highlights {
        constructor() {
            /** @type {Highlight[]} this.highlights */ this.highlights = [];
            /** @type {number} this.maxId */ this.maxId = 0;
            /** @type {boolean} this.normalDisplayMode */ this.normalDisplayMode = true;
            /** @type {number} this.reconcilingCount */ this.reconcilingCount = 0;
        }

        /**
         * Convenience function so that it will be easy for the server to setup the highlights.
         *
         * @param {number} serverHighlightId
         * @param {number} websiteId
         * @param {string} strings Actually still base64 encoded and separated by spaces
         * @param {string} comment
         */
        addFromServer(serverHighlightId, websiteId, strings, comment) {
            this.maxId++;
            this.reconcilingCount++;
            const processedStrings = strings.split(" ").map((str) => atob(str));
            let highlight = new Highlight(this, null, this.maxId, processedStrings, comment);
            highlight.serverHighlightId = serverHighlightId;
            this.highlights.push(highlight);
            highlight.active = false;
            highlight.startReconciliation();
        }

        /**
         * Tries to capture the current selection.
         *
         * @param {number} websiteId
         * @return {number} Selection index if can, -1 otherwise
         */
        capture(websiteId) {
            const strings = selectionStrings();
            if (strings === null) {
                toast.display("Can't annotate a different site. Please refresh or add a completely new website. This is due to cross-origin sharing problems.", 7000);
                return -1;
            }
            const elements = elementsFromStrings(strings);
            if (elements.length === 0) {
                toast.display("Nothing is selected! Please select some text to annotate");
                return -1;
            }
            this.maxId++;
            const highlightId = this.maxId;
            const highlight = new Highlight(this, elements, highlightId, strings, "");
            this.highlights.push(highlight);
            highlight.updateDisplayMode(this.normalDisplayMode);
            this.updateUnknownAmount();
            // TODO: add the read more section, probably in FAQ
            if (!this.highlights[this.highlights.length - 1].active) toast.display("Can't annotate! Comment is moved to unknown section. <a href='http://google.com' target='_blank'>Read more</a>");
            $.ajax({
                url: "<?php echo DOMAIN_CONTROLLER . "/addHighlight"; ?>",
                type: "POST",
                data: {
                    strings: JSON.stringify(strings),
                    websiteId: websiteId
                },
                success: (serverHighlightId) => {
                    this.get(highlightId).serverHighlightId = serverHighlightId;
                },
                error: () => {
                    toast.display("Can't connect to server to save. Please check your internet connection.");
                    this.delete(this.maxId, false);
                }
            });
            return this.highlights.length - 1;
        }

        /**
         * Toggles the display mode between normal (where known highlights are displayed) and unknown (where unknown highlights
         * are displayed).
         */
        toggleDisplayMode() {
            $(".contentBox").css("display", "none");
            $(".boundingBox").css("display", "none");
            gui.unknowns.empty();
            this.normalDisplayMode = !this.normalDisplayMode;
            for (let i = 0; i < this.highlights.length; i++) this.highlights[i].updateDisplayMode(this.normalDisplayMode);
            if (this.normalDisplayMode) gui.unknownBtn.removeClass("w3-teal w3-hover-dark-grey"); else gui.unknownBtn.addClass("w3-teal w3-hover-dark-grey");
        }

        /**
         * Convenience function
         */
        updateUnknownAmount() {
            gui.unknownAmount.html(this.highlights.reduce((accumulator, highlight) => accumulator + (highlight.active ? 0 : 1), 0));
        }

        display() {
            for (let i = 0; i < this.highlights.length; i++) this.highlights[i].display();
        }

        /**
         * Gets a Highlight with a specific id. O(n). Can be O(ln(n)), but I'm lazy.
         *
         * @return {Highlight}
         */
        get(id) {
            for (let i = 0; i < this.highlights.length; i++)
                if (this.highlights[i].id === id)
                    return this.highlights[i]
            throw "Unreachable state";
        }

        /**
         * Tries to delete a highlight with a specific id number.
         *
         * @param {number} id
         * @param {boolean} contactServer
         */
        delete(id, contactServer = true) {
            for (let i = 0; i < this.highlights.length; i++)
                if (this.highlights[i].id === id) {
                    this.highlights[i].delete(contactServer);
                    this.highlights.splice(i, 1);
                    break;
                }
            this.updateUnknownAmount();
        }

        updateHighlightComment(highlightId) {
            /** @type {Highlight} highlight */
            const highlight = this.get(highlightId);
            $.ajax({
                url: "<?php echo DOMAIN_CONTROLLER . "/updateHighlight"; ?>",
                type: "POST",
                data: {
                    highlightId: highlight.serverHighlightId,
                    comment: highlight.commentReference.val()
                },
                success: () => toast.display("Saved", 2000),
                error: () => toast.display("Can't connect to server to save. Please check your internet connection.")
            });
        }
    }

    /**
     * A simple pop up message, inspired by Android Studio's Toast. This is implemented so that it's dead simple, and you only
     * have to call display(content) to display it.
     */
    class Toast {
        constructor() {
            /** @type {number} this.instances */ this.instances = 0; // this is so that only the latest call's turnOff() will actually turn it off
        }

        /**
         * Displays toast with content.
         *
         * @param content
         * @param {number} timeout Optional time out. Defaults to 3 seconds.
         */
        display(content, timeout = 3000) {
            this.instances++;
            gui.toast.html(content);
            gui.toast.addClass("activated");
            setTimeout(this.turnOff, timeout);
        }

        /**
         * Displays a message, and keeps it online until another display() is called.
         *
         * @param {string} content
         */
        persistTillNextDisplay(content) {
            gui.toast.html(content);
            gui.toast.addClass("activated");
        }

        /**
         * Fades out the toast. Expected to be called by a timeout only.
         */
        turnOff() {
            if (toast.instances === 1)
                gui.toast.removeClass("activated");
            toast.instances--;
        }
    }

    const toast = new Toast();
</script>

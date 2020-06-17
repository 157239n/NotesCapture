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
        if (maxBottom - minTop > window.innerHeight * 0.9) return 0;
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

    class KComment {
        /**
         * Constructs a new comment. The "K" in front is to avoid namespace colliding.
         *
         * @param {number} id Comment id
         * @param {string} userName User name encoded in base 64
         * @param {string} avatarUrl Avatar url encoded in base 64
         * @param {string} formattedTime
         * @param {KComment} parentComment
         * @param {string} content Content encoded in base 64
         * @param {boolean} isOwner Whether the current user is the owner of the comment
         */
        constructor(id, userName, avatarUrl, formattedTime, parentComment, content, isOwner) {
            /** @type {number} this.commentId */ this.id = id;
            /** @type {string} this.userName */ this.userName = atob(userName);
            /** @type {string} this.avatarUrl */ this.avatarUrl = atob(avatarUrl);
            /** @type {string} this.formattedTime */ this.formattedTime = formattedTime;
            /** @type {KComment} this.parentComment */ this.parentComment = parentComment;
            /** @type {KComment} this.childComment */ this.childComment = null;
            /** @type {string} this.content */ this.content = atob(content);
            /** @type {boolean} this.owner */ this.isOwner = isOwner;
            /** @type {Highlight} this.highlight */ this.highlight = null; // to be injected by parent class
        }

        /**
         * Renders the comment.
         *
         * @return {string}
         */
        render() {
            return `
<div id="comment${this.id}Box">
    <img src="${this.avatarUrl}" alt="Avatar" class="w3-left w3-circle" width="45px" style="margin-right: 8px">` + ((this.isOwner) ? `
    <div style="float: right;position: relative">
        <span class="material-icons commentMore" style="cursor: pointer" onclick="highlights.get(${this.highlight.id}).getComment(${this.id}).openOptions()">more_vert</span>
        <div class="w3-card commentDropdown w3-round" id="commentDropdown${this.id}" style="display: none">
            <ul><li onclick="highlights.get(${this.highlight.id}).getComment(${this.id}).openEditSection()">Edit</li>
                <li onclick="highlights.get(${this.highlight.id}).getComment(${this.id}).delete()">Delete</li></ul>
        </div>
    </div>` : "") + `
    <div>${this.userName}</div>
    <div>${this.formattedTime}</div>
    <div style="clear: both;margin-bottom: 12px"></div>
    <div id="comment${this.id}Details"><div style="white-space: pre-wrap">${this.content}</div></div>
</div>`;
        }

        /**
         * Opens up the 3 vertical dots menu.
         */
        openOptions() {
            justPanel = 2;
            $(`#commentDropdown${this.id}`).css("display", "block");
        }

        /**
         * Opens the edit section for that particular comment.
         */
        openEditSection() {
            justPanel = 2;
            $(`#comment${this.id}Details`).html(`
<textarea class="w3-input w3-border w3-round comment">${this.content}</textarea>
<button class="updateComment w3-btn w3-indigo w3-round" onclick="highlights.get(${this.highlight.id}).getComment(${this.id}).update()">Update</button>
<button class="w3-btn w3-light-green w3-round" onclick="highlights.get(${this.highlight.id}).getComment(${this.id}).cancelEditComment()">Cancel</button>`);

            // make the text area auto resizeable
            $(`#comment${this.id}Details textarea.comment`).each(function () {
                this.setAttribute('style', 'height:' + (this.scrollHeight) + 'px;overflow-y:hidden;resize:none;');
            }).on('input', function () {
                this.style.height = 'auto';
                this.style.height = (this.scrollHeight) + 'px';
            });
            this.highlight.editingAComment = true;
            this.highlight.replyBoxReference.html("");
            $(".commentDropdown").css("display", "none");
            highlights.display();
        }

        cancelEditComment() {
            justPanel = 2;
            this.highlight.editingAComment = false;
            $(`#comment${this.id}Details`).html(`${this.content}`);
            highlights.display();
        }

        update() {
            this.content = $(`#comment${this.id}Details>textarea`).val();
            const commentId = this.id;
            const content = this.content;
            const highlight = this.highlight;
            $.ajax({
                url: "<?php echo DOMAIN_CONTROLLER . "/updateComment"; ?>",
                type: "POST",
                data: {
                    highlightId: highlight.serverHighlightId,
                    commentId: commentId,
                    content: content
                },
                success: () => {
                    highlight.editingAComment = false;
                    $(`#comment${commentId}Details`).html(`${content}`);
                    highlights.display();
                },
                error: () => toast.display("Can't connect to server to save. Please check your internet connection.")
            });
        }

        delete() {
            // if we're deleting the root comment of a highlight, delete the highlight altogether
            if (this.parentComment === null) {
                highlights.delete(this.highlight.id);
            } else { // if not, just delete the comment
                /** @type {KComment} self */ const self = this;
                $.ajax({
                    url: "<?php echo DOMAIN_CONTROLLER . "/deleteComment"; ?>",
                    type: "POST",
                    data: {
                        highlightId: this.highlight.serverHighlightId,
                        commentId: this.id
                    },
                    success: () => {
                        self.parentComment.childComment = self.childComment;
                        if (self.childComment !== null) self.childComment.parentComment = self.parentComment;
                        $(`div#comment${self.parentComment.id}Box+hr`).remove();
                        $(`div#comment${self.id}Box`).remove();
                    },
                    error: () => toast.display("Can't connect to server to delete. Please check your internet connection.")
                });
            }
        }
    }

    /**
     * Returns a coherent root comment, meaning all parent and child comments are linked.
     *
     * @param {KComment[]} comments
     * @return {KComment}
     */
    function coherentRootComment(comments) {
        for (let i = 0; i < comments.length - 1; i++) comments[i].childComment = comments[i + 1];
        return comments[0];
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
         * @param {KComment} rootComment Root comment object
         */
        constructor(parent, elements, id, selectedStrings, rootComment) {
            /** @type {Highlights} this.parent */ this.parent = parent;
            /** @type {Element[]} this.elements */ this.elements = elements;
            /** @type {number} this.id */ this.id = id;
            /** @type {string[]} this.selectedStrings */ this.selectedStrings = selectedStrings;
            /** @type {KComment} this.comment */ this.rootComment = rootComment;
            /** @type {boolean} this.active */ this.active = true;
            /** @type {boolean} this.focus */ this.inFocus = false;
            /** @type {boolean} this.normalDisplayMode */ this.normalDisplayMode = true;
            /** @type {number} this.serverHighlightId */ this.serverHighlightId = -1;
            /** @type {number} this.reconciliationCount */ this.reconciliationCount = 20; // twice a second, for 10 seconds
            /** @type {boolean} this.editingAComment */ this.editingAComment = false; // flag, so that the reply don't automatically pops up

            gui.panel.append(`<div class="boundingBox" id="bb${this.id}"></div>`);
            /** @type {jQuery} this.boundingBoxReference */ this.boundingBoxReference = $(`#bb${this.id}`);

            this.renderContentBox();
        }

        /**
         * Returns the leaf comment, the opposite of a root comment.
         */
        leafComment() {
            let comment = this.rootComment;
            while (comment.childComment !== null) comment = comment.childComment;
            return comment;
        }

        /**
         * To be called when the content/unknown box is clicked and currently in focus
         */
        focus() {
            if (justPanel > 0) {
                justPanel--;
                return;
            }
            justPanel = 1;
            $(".commentDropdown").css("display", "none");
            if (!this.inFocus) {
                highlights.outOfFocus();
                this.boxReference.css("left", (this.active ? "72vw" : "2vw"));
                this.boxReference.removeClass("w3-card");
                this.boxReference.addClass("w3-card-4");
                this.inFocus = true;
                if (!this.editingAComment) {
                    this.replyBoxReference.html(`<hr>
<textarea class="w3-input w3-border w3-round comment" rows="1" placeholder="Reply..."></textarea>
<button class="updateComment w3-btn w3-indigo w3-round" onclick="highlights.get(${this.id}).createNewComment()">Reply</button>
<button class="w3-btn w3-light-green w3-round" onclick="justPanel = 2;highlights.get(${this.id}).outOfFocus()">Cancel</button>`);
                    // make text areas auto resizeable
                    this.replyBoxReference.find("textarea").each(function () {
                        this.setAttribute('style', 'height:' + (this.scrollHeight) + 'px;overflow-y:hidden;resize:none;');
                    }).on('input', function () {
                        if (this.style.height !== `${this.scrollHeight}px`) {
                            this.style.height = 'auto';
                            this.style.height = (this.scrollHeight) + 'px';
                            highlights.display();
                        }
                    });
                }
                highlights.display();
            }
        }

        /**
         * To be called when the content/unknown box doesn't have the focus anymore
         */
        outOfFocus() {
            if (this.inFocus) {
                this.boxReference.css("left", (this.active ? "75vw" : "5vw"));
                this.boxReference.removeClass("w3-card-4");
                this.boxReference.addClass("w3-card");
                this.inFocus = false;
                this.replyBoxReference.html("");
                highlights.display();
            }
        }

        createNewComment() {
            /** @type {KComment} leafComment */ const leafComment = this.leafComment();
            /** @type {string} content */ const content = this.replyBoxReference.find("textarea").val();
            const highlightId = this.id;
            $.ajax({
                url: "<?php echo DOMAIN_CONTROLLER . "/addComment"; ?>",
                type: "POST",
                data: {
                    highlightId: this.serverHighlightId,
                    parentCommentId: leafComment.id,
                    content: content
                },
                success: (response) => {
                    const highlight = highlights.get(highlightId);
                    // expecting commentId (0), user's name in base64 (1), user's picture in base 64 (2), commentTime (3)
                    const splits = response.split("\n");
                    const comment = new KComment(parseInt(splits[0]), splits[1], splits[2], splits[3], leafComment, btoa(content), true);
                    comment.highlight = highlight;
                    leafComment.childComment = comment;
                    highlight.boxReference.find("div.commentsBox").append(`<hr>${comment.render()}`);
                    highlight.outOfFocus();
                },
                error: () => toast.display("Can't connect to server to add comment. Please check your internet connection.")
            });
        }

        renderCommentsAndReplyBox() {
            let comment = this.rootComment;
            if (comment === null) return;
            let content = `<div class="commentsBox">`;

            while (comment !== null) {
                comment.highlight = this;
                content += comment.render();
                comment = comment.childComment;
                if (comment !== null) content += "<hr>";
            }

            content += `</div><div class="replyBox" id="replyBox${this.id}"></div>`;
            return content;
        }

        renderContentBox() {
            gui.panel.append(`<div class="contentBox w3-border w3-round-large w3-card" id="cb${this.id}" onclick="highlights.get(${this.id}).focus()">` + this.renderCommentsAndReplyBox() + `</div>`);
            /** @type {jQuery} this.boxReference */ this.boxReference = $(`#cb${this.id}`);
            /** @type {jQuery} this.replyBoxReference */ this.replyBoxReference = $(`#replyBox${this.id}`);
        }

        renderUnknownBox() {
            // here, we're adding the unknown part
            let content = `<div class="unknownBox w3-border w3-round-large w3-card" id="ub${this.id}" onclick="highlights.get(${this.id}).focus()">
<label>Selected text</label>
<textarea class="w3-input w3-border w3-round selectedStrings" disabled>${this.selectedStrings.map(text => text.trim()).join(" ")}</textarea>`;
            content += this.renderCommentsAndReplyBox() + `</div>`;
            gui.unknowns.append(content);
            /** @type {jQuery} this.boxReference */ this.boxReference = $(`#ub${this.id}`);
            /** @type {jQuery} this.replyBoxReference */ this.replyBoxReference = $(`#replyBox${this.id}`);

            // make the text areas auto resizeable
            $(`#ub${this.id} textarea.selectedStrings`).each(function () {
                this.setAttribute('style', 'height:' + (this.scrollHeight) + 'px;overflow-y:hidden;resize:none;margin-bottom:12px;');
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
                        this.boxReference.css("top", y - this.boxReference.height() / 2);
                        this.active = true;
                        this.parent.reconcilingCount--
                        if (this.parent.reconcilingCount === 0) {
                            toast.display("Done");
                            this.parent.updateUnknownAmount();
                        }
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
                    if (this.parent.reconcilingCount === 0) {
                        toast.display("Some highlights are broken and are moved into the unknown section", 5000);
                        this.parent.updateUnknownAmount();
                    }
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
            if (this.normalDisplayMode) { // displaying normal
                if (this.active) { // this highlight is normal
                    this.boxReference.css("display", "block")
                    this.boundingBoxReference.css("display", "block")
                } else { // this highlight is unknown
                    this.boxReference.css("display", "none")
                    this.boundingBoxReference.css("display", "none")
                }
            } else { // displaying unknowns
                this.boundingBoxReference.css("display", "none")
                if (this.active) this.boxReference.css("display", "none"); // this highlight is normal
                else this.boxReference.css("display", "block"); // this highlight is unknown
            }
            if (display) this.display();
        }

        /**
         * Convenience function for testing. Switches this element to being unknown (active == false) and do other representation
         * preserving operations.
         */
        switchToUnknown() {
            this.boxReference.remove();
            this.boundingBoxReference.css("display", "none");
            this.active = false;
            this.parent.updateUnknownAmount();

            this.renderUnknownBox();
            this.updateDisplayMode(this.normalDisplayMode, true);
        }

        /**
         * Updates the position of this highlight, used when the user is scrolling.
         */
        display() {
            if (this.normalDisplayMode)
                if (this.active) {
                    const y = boundingBoxOfElements(this.elements, this.boundingBoxReference);
                    if (y === 0) this.switchToUnknown();
                    else this.boxReference.css("top", y - this.boxReference.height() / 2);
                }
        }

        /**
         * Deletes this highlight.
         */
        delete(contactServer = true) {
            this.boxReference.remove();
            this.boundingBoxReference.remove();
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

        /**
         * Gets the comment given a comment id. O(n), can be faster, but I'm lazy.
         *
         * @param {number} commentId
         * @return {KComment}
         */
        getComment(commentId) {
            let comment = this.rootComment;
            while (comment !== null) {
                if (comment.id === commentId) return comment;
                comment = comment.childComment;
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
         * @param {KComment} comment
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
            $.ajax({
                url: "<?php echo DOMAIN_CONTROLLER . "/addHighlight"; ?>",
                type: "POST",
                data: {
                    strings: JSON.stringify(strings),
                    websiteId: websiteId
                },
                success: (response) => {
                    // expecting serverHighlightId (0), commentId (1), user's name in base64 (2), user's picture in base 64 (3), commentTime (4)
                    const splits = response.split("\n");
                    const rootComment = new KComment(parseInt(splits[1]), splits[2], splits[3], splits[4], null, "", true);

                    highlights.maxId++;
                    const highlightId = this.maxId;
                    const highlight = new Highlight(highlights, elements, highlightId, strings, rootComment);
                    highlight.serverHighlightId = splits[0];
                    this.highlights.push(highlight);
                    highlight.updateDisplayMode(this.normalDisplayMode);
                    this.updateUnknownAmount();
                    if (!this.highlights[this.highlights.length - 1].active)
                        toast.display("Can't annotate! Comment is moved to unknown section. <a href='<?php echo CHARACTERISTIC_DOMAIN; ?>/faq' target='_blank'>Read more</a>");
                    else highlight.rootComment.openEditSection()
                },
                error: () => {
                    console.log("Error");
                    toast.display("Can't connect to server to save. Please check your internet connection.");
                    this.delete(this.maxId, false);
                }
            });
        }

        /**
         * Toggles the display mode between normal (where known highlights are displayed) and unknown (where unknown highlights
         * are displayed).
         */
        toggleDisplayMode() {/*
            $(".contentBox").css("display", "none");
            $(".boundingBox").css("display", "none");/**/
            //gui.unknowns.empty();
            this.normalDisplayMode = !this.normalDisplayMode;
            gui.knownToolbar.css("display", (this.normalDisplayMode ? "block" : "none"));
            gui.unknownToolbar.css("display", (this.normalDisplayMode ? "none" : "block"));
            for (let i = 0; i < this.highlights.length; i++) this.highlights[i].updateDisplayMode(this.normalDisplayMode);
            if (this.normalDisplayMode) gui.unknownBtn.removeClass("w3-teal w3-hover-dark-grey"); else gui.unknownBtn.addClass("w3-teal w3-hover-dark-grey");
        }

        /**
         * Convenience function
         */
        updateUnknownAmount() {
            const unknowns = this.highlights.reduce((accumulator, highlight) => accumulator + (highlight.active ? 0 : 1), 0);
            gui.unknownAmount.html(unknowns);
            gui.knownAmount.html(this.highlights.length - unknowns);
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

        outOfFocus() {
            for (let i = 0; i < this.highlights.length; i++) this.highlights[i].outOfFocus();
        }
    }
</script>

/**
 * Calculates the mean and standard deviation of some numbers
 *
 * @param {number[]} numbers
 * @return {number[]} mean and std
 */
function getMoments(numbers) {
    let sum = 0;
    for (let i = 0; i < numbers.length; i++) sum += numbers[i];
    const mean = sum / numbers.length;
    sum = 0.001; // prevent division by 0 when calculating z values below
    for (let i = 0; i < numbers.length; i++) sum += Math.pow((numbers[i] - mean), 2)
    const std = Math.sqrt(sum / numbers.length);
    return [mean, std];
}

/**
 * Get Z values.
 *
 * @param {number[]} numbers
 * @return {number[]} z values
 */
function getZValues(numbers) {
    const moments = getMoments(numbers);
    //console.log(moments);
    const zValues = [];
    for (let i = 0; i < numbers.length; i++) zValues[i] = (numbers[i] - moments[0]) / moments[1];
    return zValues;
}

/**
 * Find elements that have the embedded strings, 1 element for each string. If strings is empty, then returns an empty array.
 *
 * @param {string[]} strings
 * @return {Element[]}
 */
function elementsFromStrings(strings) {
    let elements = [];
    for (let i = 0; i < strings.length; i++) {
        let elems = findDistinctElems(strings[i]);
        if (elems.length > 0) elements.push(elems[0]);
    }
    return elements;
}

/**
 * Draws a bounding box given elements. Will filter out outliers at 2 sigmas and above. Returns the center y coordinate
 * of the bounding box when operation is successful.
 *
 * This operation may fail when:
 * - Standard deviation after removing outliers larger than limit (300)
 * - There are no elements left after removing outliers
 *
 * @param {Element[]} elements
 * @param {jQuery} boundingBox
 * @return {number} 0 if unsuccessful, bounding box center y coordinate if successful
 */
function boundingBoxOfElements(elements, boundingBox) {
    const sigma = 2, maxStd = 300;
    // get rectangles
    /** @type {DOMRect[]} rects */ const rects = [];
    for (let i = 0; i < elements.length; i++) rects.push(elements[i].getBoundingClientRect());
    // filter outliers with z value more than 2
    /** @type {number[]} meanHeights */ let meanHeights = rects.map(location => (location.top + location.bottom) / 2);
    const zValues = getZValues(meanHeights);//console.log(elements);console.log(zValues);
    /** @type {DOMRect[]} filteredRects */ let filteredRects = []; // below to fix corner case of <script> tag
    for (let i = 0; i < elements.length; i++) if (Math.abs(zValues[i]) < sigma && (rects[i].top !== 0 || rects[i].top !== 0)) filteredRects.push(rects[i]);
    // check whether spread is still too wide:
    if (getMoments(filteredRects.map(location => (location.top + location.bottom) / 2))[1] > maxStd) return 0;
    if (filteredRects.length === 0) return 0;
    // calculate bounding box and draw it
    const minTop = filteredRects.reduce((accumulator, value) => Math.min(accumulator, value.top), filteredRects[0].top);
    const maxBottom = filteredRects.reduce((accumulator, value) => Math.max(accumulator, value.bottom), filteredRects[0].bottom);
    const minLeft = filteredRects.reduce((accumulator, value) => Math.min(accumulator, value.left), filteredRects[0].left);
    const maxRight = filteredRects.reduce((accumulator, value) => Math.max(accumulator, value.right), filteredRects[0].right);
    boundingBox.css("left", minLeft).css("top", minTop).css("width", maxRight - minLeft).css("height", maxBottom - minTop);
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
    return filterParents($("#page").contents().find(":contains('" + str.replace(/[#;&,\.\+\*~':"!\^\$\[\]\(\)=>|\/\\]/g, '\\$&') + "')"));
}

/**
 * Extracts texts from a node
 *
 * @param {Node} node
 * @returns {string[]}
 */
function extractText(node) {
    let texts = [];
    if (node.childNodes.length === 0) return [node.textContent];
    for (let i = 0; i < node.childNodes.length; i++) texts = texts.concat(extractText(node.childNodes[i]));
    return texts.filter(text => text !== "\n");
}

/**
 * Extracts texts from the current selection. If nothing is selected, returns empty array.
 *
 * @returns {string[]}
 */
function selectionStrings() {
    const sel = document.getElementById("page").contentWindow.getSelection();
    // noinspection EqualityComparisonWithCoercionJS
    if (sel == false) return [];
    return extractText(sel.getRangeAt(0).cloneContents());
}

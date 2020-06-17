<script>
    /**
     * A simple pop up message, inspired by Android Studio's Toast. This is implemented so that it's dead simple, and you only
     * have to call display(content) to display it.
     */
    class Toast {
        constructor() {
            /** @type {number} this.instances */ this.instances = 0; // this is so that only the latest call's turnOff() will actually turn it off
            /** @type {jQuery} this.objectReference */ this.objectReference = $("#toast");
        }

        /**
         * Displays toast with content.
         *
         * @param content
         * @param {number} timeout Optional time out. Defaults to 3 seconds.
         */
        display(content, timeout = 3000) {
            this.instances++;
            this.objectReference.html(content);
            this.objectReference.addClass("activated");
            setTimeout(this.turnOff, timeout);
        }

        /**
         * Displays a message, and keeps it online until another display() is called.
         *
         * @param {string} content
         */
        persistTillNextDisplay(content) {
            this.objectReference.html(content);
            this.objectReference.addClass("activated");
        }

        /**
         * Fades out the toast. Expected to be called by a timeout only.
         */
        turnOff() {
            if (toast.instances === 1)
                toast.objectReference.removeClass("activated");
            toast.instances--;
        }
    }

    /** @type {Toast} toast */ const toast = new Toast();

    class Tooltip {
        constructor() {
            /** @type {number} this.instances */ this.instances = 0; // this is so that only the latest call's deactivate() will actually turn it off
            /** @type {jQuery} this.objectReference */ this.objectReference = $("#tooltip");
            /** @type {boolean} this.displaying */ this.displaying = false;
            this.lastX = 0;
            this.lastY = 0;
        }

        /**
         * Activates the tooltip with a specific tip, and displays until deactivate() is called.
         *
         * @param {string} tip
         */
        activate(tip) {
            this.instances++;
            this.objectReference.css("display", "block");
            this.objectReference.html(tip);
            this.objectReference.addClass("activated");
            this.displaying = true;
            this.run(null);
        }

        deactivate() {
            this.instances--;
            this.objectReference.removeClass("activated");
            setTimeout(() => {
                if (this.instances === 0) {
                    tooltip.displaying = false;
                    tooltip.objectReference.css("display", "none");
                }
            }, 300);
        }

        /**
         * Called by the environment every tick
         */
        run(event) {
            if (this.displaying) {
                this.objectReference.css("top", `${this.lastY + 15}px`);
                this.objectReference.css("left", `${this.lastX + 15}px`);
            }
            if (event !== null) {
                this.lastX = event.clientX;
                this.lastY = event.clientY;
            }
        }
    }

    /** @type {Tooltip} tooltip */ const tooltip = new Tooltip()
    document.addEventListener('mousemove', (event) => tooltip.run(event));
</script>

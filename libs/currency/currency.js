/**
 * All credit goes to https://github.com/lagden/currency
 *
 * It works perfect with some tweaks.
 */
class Currency {
    unmaskedValue = 0

    constructor(input, opts = {}) {
        this.opts = {
            keyEvent: 'input',
            triggerOnBlur: false,
            init: false,
            backspace: false,
            maskOpts: {},
            ...opts,
        }

        if (input instanceof HTMLInputElement === false) {
            throw new TypeError('The input should be a HTMLInputElement')
        }

        // Add fraction on initial value if missing
        const parts = String(input.value).split('.')
        input.value = parts.length === 1 ? `${parts.shift()}.00` : `${parts.shift()}.${parts.pop().padEnd(2, '0')}`

        this.input = input
        this.events = new Set()

        // Initialize
        if (this.opts.init) {
            this.input.value = Currency.masking(this.input.value, this.opts.maskOpts)
        }

        // Listener
        this.input.addEventListener(this.opts.keyEvent, this)
        this.events.add(this.opts.keyEvent)

        this.input.addEventListener('click', this)
        this.events.add('click')

        if (this.opts.triggerOnBlur) {
            this.input.addEventListener('blur', this)
            this.events.add('blur')
        }
    }

    static getUnmasked() {
        return this.unmaskedValue
    }

    static position(v) {
        const nums = new Set(['1', '2', '3', '4', '5', '6', '7', '8', '9', '0'])
        const len = v.length

        let cc = 0
        for (let i = len - 1; i >= 0; i--) {
            if (nums.has(v[i])) {
                break
            }
            cc++
        }

        return String(v).length - cc
    }

    static masking(v, opts = {}) {
        const {
            empty = false,
            locales = 'pt-BR',
            options = {
                minimumFractionDigits: 2,
            },
        } = opts

        if (typeof v === 'number') {
            v = v.toFixed(2)
        }

        const n = String(v).replace(/\D/g, '').replace(/^0+/g, '')
        const t = n.padStart(3, '0')
        const d = t.slice(-2)
        const i = t.slice(0, t.length - 2)

        if (empty && i === '0' && d === '00') {
            return ''
        }

        this.unmaskedValue = `${i}.${d}`

        return new Intl.NumberFormat(locales, options).format(this.unmaskedValue)
    }

    onMasking(event) {
        if (this.opts.backspace && event?.inputType === 'deleteContentBackward') {
            return
        }

        this.input.value = Currency.masking(this.input.value, this.opts.maskOpts)
        const pos = Currency.position(this.input.value)
        this.input.setSelectionRange(pos, pos)
    }

    onClick() {
        const pos = Currency.position(this.input.value)
        this.input.focus()
        this.input.setSelectionRange(pos, pos)
    }

    destroy() {
        for (const _event of this.events) {
            this.input.removeEventListener(_event, this)
        }
    }

    handleEvent(event) {
        if (event.type === 'click') {
            this.onClick(event)
        } else {
            this.onMasking(event)
        }
    }
}

if (!window.Currency) {
    window.Currency = Currency
}


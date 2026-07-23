<x-layouts.mobile :title="__('site.validate_citizen')">
    <div x-data="citizenValidator()" x-init="init()">
        <a href="{{ route('agent.dashboard') }}" class="text-sm text-slate-500 mb-3 inline-block">← {{ __('site.back_to_dashboard') }}</a>

        <form @submit.prevent="submit()" class="bg-white rounded-2xl shadow p-4 mb-4">
            <label class="block text-sm font-medium text-slate-700 mb-2">{{ __('site.national_id_mobile_card_qr') }}</label>
            <input
                type="text"
                x-model="identifier"
                x-ref="identifierInput"
                autofocus
                inputmode="text"
                placeholder="{{ __('site.type_or_scan') }}"
                class="w-full rounded-xl border border-slate-300 px-4 py-3 text-lg mb-3"
                style="min-height:48px">
            <div class="grid grid-cols-2 gap-3">
                <button type="button" @click="toggleScan()"
                    class="tap-target rounded-xl bg-slate-800 text-white font-semibold text-base">
                    <span x-text="scanning ? t('stop_camera') : t('scan_qr')"></span>
                </button>
                <button type="submit" :disabled="loading || !identifier"
                    class="tap-target rounded-xl bg-blue-600 text-white font-semibold text-base disabled:opacity-50">
                    <span x-text="loading ? t('checking') : t('check')"></span>
                </button>
            </div>
        </form>

        <!-- QR scanner container -->
        <div id="qr-reader" x-show="scanning" class="mt-4 rounded-xl overflow-hidden bg-black">
            <div id="qr-reader-render"></div>
        </div>

        <!-- Toast notification for success/error -->
        <div x-show="toast" x-transition.duration.300ms
             :class="toastType === 'success' ? 'bg-green-600' : 'bg-red-600'"
             class="fixed top-4 left-1/2 -translate-x-1/2 text-white px-5 py-3 rounded-xl shadow-lg z-50 font-medium"
             x-text="toastMsg"></div>

        <template x-if="result">
            <div>
                <div x-show="result.status === 'approved'" class="rounded-2xl p-6 text-white text-center shadow-lg mt-3" style="background-color:#16a34a">
                    <p class="text-sm uppercase tracking-wide opacity-90">{{ __('site.approved') }}</p>
                    <p class="text-2xl font-bold mt-1" x-text="result.citizen_name"></p>
                    <p class="text-5xl font-extrabold my-4" x-text="result.allocated_blocks + ' ' + t('blocks')"></p>
                    <button @click="claim()" :disabled="claiming"
                        class="tap-target w-full rounded-xl bg-white text-green-700 font-bold text-lg py-3 mt-2 disabled:opacity-60">
                        <span x-text="claiming ? t('confirming') : t('confirm_delivery')"></span>
                    </button>
                </div>

                <div x-show="result.status === 'claimed'" class="rounded-2xl p-6 text-white text-center shadow-lg mt-3" style="background-color:#dc2626">
                    <p class="text-sm uppercase tracking-wide opacity-90">{{ __('site.already_claimed') }}</p>
                    <p class="text-2xl font-bold mt-1" x-text="result.citizen_name"></p>
                    <p class="text-base mt-3" x-text="message"></p>
                </div>

                <div x-show="result.status === 'not_found'" class="rounded-2xl p-6 text-white text-center shadow-lg mt-3" style="background-color:#ca8a04">
                    <p class="text-sm uppercase tracking-wide opacity-90">{{ __('site.not_registered') }}</p>
                    <p class="text-base mt-2">{{ __('site.citizen_not_found_message') }}</p>
                </div>

                <div x-show="result.status === 'expired'" class="rounded-2xl p-6 text-white text-center shadow-lg mt-3" style="background-color:#dc2626">
                    <p class="text-sm uppercase tracking-wide opacity-90">{{ __('site.expired') }}</p>
                    <p class="text-base mt-2">{{ __('site.ticket_expired_message') }}</p>
                </div>

                <div x-show="justClaimed" class="rounded-2xl p-6 text-white text-center shadow-lg mt-3" style="background-color:#16a34a">
                    {{ __('site.delivery_confirmed_ready_next') }}
                </div>
            </div>
        </template>

        <div x-show="error" class="rounded-2xl p-4 bg-red-100 text-red-700 text-center font-medium mt-3" x-text="error"></div>
    </div>

    {{-- QR scanner library --}}
    <script src="https://cdn.jsdelivr.net/npm/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

    {{-- Register Alpine component BEFORE Alpine initializes --}}
    <script>
        // Global function for Alpine component
        window.citizenValidator = function() {
            // Translations injected from PHP so Alpine JS can use them
            const I18N = {{ collect([
                'stop_camera' => __('site.stop_camera'),
                'scan_qr' => __('site.scan_qr'),
                'checking' => __('site.checking'),
                'check' => __('site.check'),
                'confirming' => __('site.confirming'),
                'confirm_delivery' => __('site.confirm_delivery'),
                'network_error' => __('site.network_error'),
                'could_not_confirm_delivery' => __('site.could_not_confirm_delivery'),
                'could_not_access_camera' => __('site.could_not_access_camera'),
                'something_went_wrong' => __('site.something_went_wrong'),
                'delivery_confirmed' => __('site.delivery_confirmed_ready_next'),
                'blocks' => __('site.blocks'),
                'claim_succeeded' => __('site.delivery_confirmed'),
            ])->toJson() }};

            return {
                identifier: '',
                loading: false,
                claiming: false,
                result: null,
                message: '',
                error: null,
                scanning: false,
                justClaimed: false,
                html5QrCode: null,
                toast: false,
                toastMsg: '',
                toastType: 'success',
                toastTimer: null,

                t(key) {
                    return I18N[key] || key;
                },

                init() {
                    this.$refs.identifierInput.focus();
                },

                showToast(msg, type = 'success') {
                    clearTimeout(this.toastTimer);
                    this.toastMsg = msg;
                    this.toastType = type;
                    this.toast = true;
                    this.toastTimer = setTimeout(() => { this.toast = false; }, 2500);
                },

                csrfToken() {
                    return document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                },

                async submit() {
                    if (!this.identifier) return;
                    this.loading = true;
                    this.error = null;
                    this.result = null;
                    this.justClaimed = false;

                    try {
                        const res = await fetch('{{ route('agent.tickets.validate') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': this.csrfToken(),
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({ identifier: this.identifier }),
                        });
                        const json = await res.json();

                        if (!res.ok || !json.success) {
                            this.error = json.message || this.t('something_went_wrong');
                            return;
                        }

                        this.result = json.data;
                        this.message = json.message;
                    } catch (e) {
                        this.error = this.t('network_error');
                    } finally {
                        this.loading = false;
                    }
                },

                async claim() {
                    if (!this.result || !this.result.ticket_id) return;
                    this.claiming = true;
                    this.error = null;

                    try {
                        const res = await fetch('/agent/tickets/' + this.result.ticket_id + '/claim', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': this.csrfToken(),
                                'Accept': 'application/json',
                            },
                        });
                        const json = await res.json();

                        if (!res.ok || !json.success) {
                            this.error = json.message || this.t('could_not_confirm_delivery');
                            this.showToast(this.error, 'error');
                            if (json.data && json.data.status === 'claimed') {
                                this.result.status = 'claimed';
                                if (json.data.claimed_at) {
                                    this.result.claimed_at = json.data.claimed_at;
                                }
                            }
                            return;
                        }

                        this.result.status = 'claimed';
                        this.justClaimed = true;
                        this.showToast(this.t('claim_succeeded'), 'success');

                        setTimeout(() => {
                            this.identifier = '';
                            this.result = null;
                            this.justClaimed = false;
                            this.$refs.identifierInput.focus();
                        }, 1800);
                    } catch (e) {
                        this.error = this.t('network_error');
                        this.showToast(this.error, 'error');
                    } finally {
                        this.claiming = false;
                    }
                },

                toggleScan() {
                    this.scanning ? this.stopScan() : this.startScan();
                },

                startScan() {
                    this.scanning = true;
                    this.error = null;
                    this.$nextTick(() => {
                        try {
                            const containerId = 'qr-reader-render';
                            if (typeof Html5Qrcode === 'undefined') {
                                this.error = this.t('could_not_access_camera');
                                this.scanning = false;
                                return;
                            }
                            this.html5QrCode = new Html5Qrcode(containerId);
                            this.html5QrCode.start(
                                { facingMode: 'environment' },
                                { fps: 10, qrbox: 220 },
                                (decodedText) => {
                                    this.identifier = decodedText;
                                    this.stopScan();
                                    this.submit();
                                },
                                () => {}
                            ).catch(() => {
                                this.error = this.t('could_not_access_camera');
                                this.scanning = false;
                            });
                        } catch (e) {
                            this.error = this.t('could_not_access_camera');
                            this.scanning = false;
                        }
                    });
                },

                stopScan() {
                    if (this.html5QrCode) {
                        this.html5QrCode.stop().then(() => {
                            this.html5QrCode.clear();
                        }).catch(() => {});
                        this.html5QrCode = null;
                    }
                    this.scanning = false;
                },
            };
        };
    </script>
</x-layouts.mobile>

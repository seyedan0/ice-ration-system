<x-layouts.mobile title="Validate Citizen">
    <div x-data="citizenValidator()" x-init="init()">
        <a href="{{ route('agent.dashboard') }}" class="text-sm text-slate-500 mb-3 inline-block">← Back to dashboard</a>

        <form @submit.prevent="submit()" class="bg-white rounded-2xl shadow p-4 mb-4">
            <label class="block text-sm font-medium text-slate-700 mb-2">National ID, Mobile, or Card QR</label>
            <input
                type="text"
                x-model="identifier"
                x-ref="identifierInput"
                autofocus
                inputmode="text"
                placeholder="Type or scan..."
                class="w-full rounded-xl border border-slate-300 px-4 py-3 text-lg mb-3"
                style="min-height:48px"
            >
            <div class="grid grid-cols-2 gap-3">
                <button type="button" @click="toggleScan()"
                    class="tap-target rounded-xl bg-slate-800 text-white font-semibold text-base">
                    <span x-text="scanning ? '✕ Stop Camera' : '📷 Scan QR'"></span>
                </button>
                <button type="submit" :disabled="loading || !identifier"
                    class="tap-target rounded-xl bg-blue-600 text-white font-semibold text-base disabled:opacity-50">
                    <span x-text="loading ? 'Checking...' : 'Check'"></span>
                </button>
            </div>
            <div id="qr-reader" class="mt-4 rounded-xl overflow-hidden" x-show="scanning"></div>
        </form>

        <template x-if="result">
            <div>
                <div x-show="result.status === 'approved'" class="rounded-2xl p-6 text-white text-center shadow-lg" style="background-color:#16a34a">
                    <p class="text-sm uppercase tracking-wide opacity-90">Approved</p>
                    <p class="text-2xl font-bold mt-1" x-text="result.citizen_name"></p>
                    <p class="text-5xl font-extrabold my-4" x-text="result.allocated_blocks + ' Blocks'"></p>
                    <button @click="claim()" :disabled="claiming"
                        class="tap-target w-full rounded-xl bg-white text-green-700 font-bold text-lg py-3 mt-2 disabled:opacity-60">
                        <span x-text="claiming ? 'Confirming...' : '✓ Confirm Delivery'"></span>
                    </button>
                </div>

                <div x-show="result.status === 'claimed'" class="rounded-2xl p-6 text-white text-center shadow-lg" style="background-color:#dc2626">
                    <p class="text-sm uppercase tracking-wide opacity-90">Already Claimed</p>
                    <p class="text-2xl font-bold mt-1" x-text="result.citizen_name"></p>
                    <p class="text-base mt-3" x-text="message"></p>
                </div>

                <div x-show="result.status === 'not_found'" class="rounded-2xl p-6 text-white text-center shadow-lg" style="background-color:#ca8a04">
                    <p class="text-sm uppercase tracking-wide opacity-90">Not Registered</p>
                    <p class="text-base mt-2">This identifier does not match any active citizen.</p>
                </div>

                <div x-show="result.status === 'expired'" class="rounded-2xl p-6 text-white text-center shadow-lg" style="background-color:#dc2626">
                    <p class="text-sm uppercase tracking-wide opacity-90">Expired</p>
                    <p class="text-base mt-2">This ticket is no longer valid.</p>
                </div>

                <div x-show="justClaimed" class="rounded-2xl p-6 text-white text-center shadow-lg mt-3" style="background-color:#16a34a">
                    ✓ Delivery confirmed! Ready for next citizen.
                </div>
            </div>
        </template>

        <div x-show="error" class="rounded-2xl p-4 bg-red-100 text-red-700 text-center font-medium mt-3" x-text="error"></div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <script>
        function citizenValidator() {
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

                init() {
                    this.$refs.identifierInput.focus();
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
                            this.error = json.message || 'Something went wrong.';
                            return;
                        }

                        this.result = json.data;
                        this.message = json.message;
                    } catch (e) {
                        this.error = 'Network error. Please try again.';
                    } finally {
                        this.loading = false;
                    }
                },

                async claim() {
                    if (!this.result || !this.result.ticket_id) return;
                    this.claiming = true;
                    this.error = null;

                    try {
                        const res = await fetch(`/agent/tickets/${this.result.ticket_id}/claim`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': this.csrfToken(),
                                'Accept': 'application/json',
                            },
                        });
                        const json = await res.json();

                        if (!res.ok || !json.success) {
                            this.error = json.message || 'Could not confirm delivery.';
                            if (json.data && json.data.status === 'claimed') {
                                this.result.status = 'claimed';
                            }
                            return;
                        }

                        this.justClaimed = true;
                        setTimeout(() => {
                            this.identifier = '';
                            this.result = null;
                            this.justClaimed = false;
                            this.$refs.identifierInput.focus();
                        }, 1800);
                    } catch (e) {
                        this.error = 'Network error. Please try again.';
                    } finally {
                        this.claiming = false;
                    }
                },

                toggleScan() {
                    this.scanning ? this.stopScan() : this.startScan();
                },

                startScan() {
                    this.scanning = true;
                    this.$nextTick(() => {
                        this.html5QrCode = new Html5Qrcode('qr-reader');
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
                            this.error = 'Could not access camera.';
                            this.scanning = false;
                        });
                    });
                },

                stopScan() {
                    if (this.html5QrCode) {
                        this.html5QrCode.stop().catch(() => {});
                    }
                    this.scanning = false;
                },
            };
        }
    </script>
</x-layouts.mobile>

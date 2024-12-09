<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            إرسال رسالة نصية
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <!-- رسائل النجاح والخطأ -->
                    @if(session('error'))
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                            <strong class="font-bold">خطأ!</strong>
                            <span class="block sm:inline">{{ session('error') }}</span>
                        </div>
                    @endif

                    @if(session('success'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                            <strong class="font-bold">تم بنجاح!</strong>
                            <span class="block sm:inline">{{ session('success') }}</span>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('sms.send') }}" id="smsForm">
                        @csrf
                        <div class="mb-4">
                            <x-input-label for="phone" value="رقم الهاتف" />
                            <x-text-input id="phone" 
                                name="phone"
                                type="text"
                                class="mt-1 block w-full"
                                placeholder="مثال: 97449907"
                                required
                            />
                            <x-input-error class="mt-2" :messages="$errors->get('phone')" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="message" value="نص الرسالة" />
                            <textarea
                                id="message"
                                name="message"
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                rows="4"
                                required
                                maxlength="160"
                            ></textarea>
                            <x-input-error class="mt-2" :messages="$errors->get('message')" />
                            <div class="mt-2 text-sm text-gray-500">
                                <span id="charCount">0/160</span> حرف
                            </div>
                        </div>

                        <div class="flex items-center gap-4">
                            <x-primary-button>إرسال</x-primary-button>
                        </div>
                    </form>

                    <!-- قسم التصحيح -->
                    @if(session('debug'))
                        <div class="mt-8 p-4 bg-gray-100 rounded-lg">
                            <h3 class="font-semibold mb-2">تفاصيل الإرسال:</h3>
                            <pre class="text-sm overflow-auto">{{ json_encode(session('debug'), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const message = document.getElementById('message');
            const charCount = document.getElementById('charCount');

            message.addEventListener('input', function() {
                const count = this.value.length;
                charCount.textContent = count + '/160';
                if (count > 160) {
                    charCount.classList.add('text-red-500');
                } else {
                    charCount.classList.remove('text-red-500');
                }
            });
        });
    </script>
</x-app-layout>

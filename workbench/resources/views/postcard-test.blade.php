<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Swiss Post Postcard API Test Interface</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
</head>

<body class="bg-gray-50">
    <div class="min-h-screen" x-data="postcardTester()">
        <!-- Header -->
        <header class="bg-white shadow-sm border-b">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center py-6">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">
                            <i class="fas fa-mail-bulk text-red-600 mr-3"></i>
                            Swiss Post Postcard API Tester
                        </h1>
                        <p class="text-gray-600 mt-1">Test and validate your postcard API integration</p>
                    </div>
                    <div class="flex items-center space-x-2">
                        <button @click="testRawOAuth()"
                            class="bg-yellow-600 hover:bg-yellow-700 text-white px-3 py-2 rounded-lg transition-colors text-sm">
                            <i class="fas fa-network-wired mr-1"></i>Raw OAuth
                        </button>
                        <button @click="debugOAuth()"
                            class="bg-purple-600 hover:bg-purple-700 text-white px-3 py-2 rounded-lg transition-colors text-sm">
                            <i class="fas fa-bug mr-1"></i>Debug OAuth
                        </button>
                        <button @click="loadCampaignStats()"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors">
                            <i class="fas fa-chart-bar mr-2"></i>Check Campaign
                        </button>
                    </div>
                </div>
            </div>
        </header>

        <!-- Campaign Stats Banner -->
        <div x-show="campaignStats" x-cloak class="bg-blue-50 border-b border-blue-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-blue-600" x-text="campaignStats?.quota || 0"></div>
                        <div class="text-sm text-gray-600">Total Quota</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-green-600" x-text="campaignStats?.sendPostcards || 0"></div>
                        <div class="text-sm text-gray-600">Sent</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-orange-600"
                            x-text="campaignStats?.freeToSendPostcards || 0"></div>
                        <div class="text-sm text-gray-600">Remaining</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold"
                            :class="campaignStats?.usagePercentage > 80 ? 'text-red-600' : 'text-blue-600'"
                            x-text="(campaignStats?.usagePercentage || 0) + '%'"></div>
                        <div class="text-sm text-gray-600">Usage</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Tab Navigation -->
            <div class="border-b border-gray-200 mb-8">
                <nav class="-mb-px flex space-x-8">
                    <button @click="activeTab = 'quick-send'"
                        :class="activeTab === 'quick-send' ? 'border-red-500 text-red-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm transition-colors">
                        <i class="fas fa-rocket mr-2"></i>Quick Send
                    </button>
                    <button @click="activeTab = 'step-by-step'"
                        :class="activeTab === 'step-by-step' ? 'border-red-500 text-red-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm transition-colors">
                        <i class="fas fa-list-ol mr-2"></i>Step by Step
                    </button>
                    <button @click="activeTab = 'validation'"
                        :class="activeTab === 'validation' ? 'border-red-500 text-red-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm transition-colors">
                        <i class="fas fa-check-circle mr-2"></i>Validation
                    </button>
                    <button @click="activeTab = 'branding'"
                        :class="activeTab === 'branding' ? 'border-red-500 text-red-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm transition-colors">
                        <i class="fas fa-paint-brush mr-2"></i>Branding
                    </button>
                </nav>
            </div>

            <!-- Alert Messages -->
            <div x-show="alert.show" x-cloak
                :class="alert.type === 'success' ? 'bg-green-50 border-green-200 text-green-800' : 'bg-red-50 border-red-200 text-red-800'"
                class="border rounded-lg p-4 mb-6">
                <div class="flex items-start">
                    <i :class="alert.type === 'success' ? 'fas fa-check-circle text-green-400' : 'fas fa-exclamation-circle text-red-400'"
                        class="mr-3 mt-0.5"></i>
                    <div class="flex-1">
                        <h4 class="font-medium" x-text="alert.title"></h4>
                        <p class="mt-1" x-text="alert.message"></p>
                        <div x-show="alert.details" class="mt-2">
                            <details class="cursor-pointer">
                                <summary class="text-sm font-medium">Show Details</summary>
                                <pre class="mt-2 text-xs bg-white p-2 rounded border overflow-auto"
                                    x-text="alert.details"></pre>
                            </details>
                        </div>
                    </div>
                    <button @click="alert.show = false" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>

            <!-- Tab Content -->
            <div class="space-y-8">
                <!-- Quick Send Tab -->
                <div x-show="activeTab === 'quick-send'" x-cloak>
                    <div class="bg-white rounded-lg shadow-sm border p-6">
                        <h2 class="text-xl font-semibold text-gray-900 mb-6">
                            <i class="fas fa-rocket text-red-600 mr-2"></i>
                            Quick Send Postcard
                        </h2>
                        <p class="text-gray-600 mb-6">Create and send a postcard in one step</p>

                        <form @submit.prevent="submitQuickSend()">
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                                <!-- Recipient Address -->
                                <div class="space-y-4">
                                    <h3 class="text-lg font-medium text-gray-900">Recipient Address</h3>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">First Name
                                                *</label>
                                            <input type="text" x-model="quickSend.recipient.firstname" required
                                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Last Name
                                                *</label>
                                            <input type="text" x-model="quickSend.recipient.lastname" required
                                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent">
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-3 gap-4">
                                        <div class="col-span-2">
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Street *</label>
                                            <input type="text" x-model="quickSend.recipient.street" required
                                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">House Nr
                                                *</label>
                                            <input type="text" x-model="quickSend.recipient.houseNr" required
                                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent">
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-3 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">ZIP *</label>
                                            <input type="text" x-model="quickSend.recipient.zip" required
                                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">City *</label>
                                            <input type="text" x-model="quickSend.recipient.city" required
                                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Country</label>
                                            <select x-model="quickSend.recipient.country"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent">
                                                <option value="CH">Switzerland</option>
                                                <option value="DE">Germany</option>
                                                <option value="AT">Austria</option>
                                                <option value="FR">France</option>
                                                <option value="IT">Italy</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- Postcard Content -->
                                <div class="space-y-4">
                                    <h3 class="text-lg font-medium text-gray-900">Postcard Content</h3>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Image (1819×1311 px)
                                            *</label>
                                        <input type="file" x-ref="quickSendImage" accept="image/jpeg,image/png" required
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent">
                                        <p class="text-xs text-gray-500 mt-1">JPEG or PNG, max 10MB</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Sender Text
                                            *</label>
                                        <textarea x-model="quickSend.sender_text" required rows="4"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent"
                                            placeholder="Your message to the recipient..."></textarea>
                                        <p class="text-xs text-gray-500 mt-1">Max 2000 characters</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Campaign Key
                                            (optional)</label>
                                        <input type="text" x-model="quickSend.campaign_key"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent"
                                            placeholder="Leave empty to use default campaign">
                                    </div>
                                </div>
                            </div>

                            <div class="mt-8 flex justify-end">
                                <button type="submit" :disabled="loading"
                                    class="bg-red-600 hover:bg-red-700 disabled:bg-gray-400 text-white px-6 py-3 rounded-lg font-medium transition-colors">
                                    <i class="fas fa-paper-plane mr-2"></i>
                                    <span x-show="!loading">Create Postcard</span>
                                    <span x-show="loading">Creating...</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Step by Step Tab -->
                <div x-show="activeTab === 'step-by-step'" x-cloak>
                    <div class="space-y-6">
                        <!-- Step 1: Create Postcard -->
                        <div class="bg-white rounded-lg shadow-sm border">
                            <div class="p-6 border-b border-gray-200">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <div
                                            class="flex-shrink-0 w-8 h-8 bg-blue-600 text-white rounded-full flex items-center justify-center font-medium text-sm">
                                            1</div>
                                        <h3 class="ml-3 text-lg font-medium text-gray-900">Create Postcard</h3>
                                    </div>
                                    <button @click="toggleStep('create')" class="text-gray-400 hover:text-gray-600">
                                        <i :class="stepOpen.create ? 'fas fa-chevron-up' : 'fas fa-chevron-down'"></i>
                                    </button>
                                </div>
                            </div>
                            <div x-show="stepOpen.create" x-collapse>
                                <div class="p-6">
                                    <form @submit.prevent="createPostcard()">
                                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                            <!-- Recipient Address (repeated structure) -->
                                            <div class="space-y-4">
                                                <h4 class="font-medium text-gray-900">Recipient Address</h4>
                                                <!-- Address fields similar to quick send -->
                                                <div class="grid grid-cols-2 gap-4">
                                                    <div>
                                                        <label
                                                            class="block text-sm font-medium text-gray-700 mb-1">First
                                                            Name *</label>
                                                        <input type="text" x-model="stepByStep.recipient.firstname"
                                                            required
                                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                                    </div>
                                                    <div>
                                                        <label class="block text-sm font-medium text-gray-700 mb-1">Last
                                                            Name *</label>
                                                        <input type="text" x-model="stepByStep.recipient.lastname"
                                                            required
                                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                                    </div>
                                                </div>
                                                <div class="grid grid-cols-3 gap-4">
                                                    <div class="col-span-2">
                                                        <label
                                                            class="block text-sm font-medium text-gray-700 mb-1">Street
                                                            *</label>
                                                        <input type="text" x-model="stepByStep.recipient.street"
                                                            required
                                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                                    </div>
                                                    <div>
                                                        <label
                                                            class="block text-sm font-medium text-gray-700 mb-1">House
                                                            Nr *</label>
                                                        <input type="text" x-model="stepByStep.recipient.houseNr"
                                                            required
                                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                                    </div>
                                                </div>
                                                <div class="grid grid-cols-3 gap-4">
                                                    <div>
                                                        <label class="block text-sm font-medium text-gray-700 mb-1">ZIP
                                                            *</label>
                                                        <input type="text" x-model="stepByStep.recipient.zip" required
                                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                                    </div>
                                                    <div>
                                                        <label class="block text-sm font-medium text-gray-700 mb-1">City
                                                            *</label>
                                                        <input type="text" x-model="stepByStep.recipient.city" required
                                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                                    </div>
                                                    <div>
                                                        <label
                                                            class="block text-sm font-medium text-gray-700 mb-1">Country</label>
                                                        <select x-model="stepByStep.recipient.country"
                                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                                            <option value="CH">Switzerland</option>
                                                            <option value="DE">Germany</option>
                                                            <option value="AT">Austria</option>
                                                            <option value="FR">France</option>
                                                            <option value="IT">Italy</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Sender Address (optional) -->
                                            <div class="space-y-4">
                                                <h4 class="font-medium text-gray-900">Sender Address (optional)</h4>
                                                <div class="grid grid-cols-2 gap-4">
                                                    <div>
                                                        <label
                                                            class="block text-sm font-medium text-gray-700 mb-1">First
                                                            Name</label>
                                                        <input type="text" x-model="stepByStep.sender.firstname"
                                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                                    </div>
                                                    <div>
                                                        <label class="block text-sm font-medium text-gray-700 mb-1">Last
                                                            Name</label>
                                                        <input type="text" x-model="stepByStep.sender.lastname"
                                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                                    </div>
                                                </div>
                                                <div class="grid grid-cols-3 gap-4">
                                                    <div class="col-span-2">
                                                        <label
                                                            class="block text-sm font-medium text-gray-700 mb-1">Street</label>
                                                        <input type="text" x-model="stepByStep.sender.street"
                                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                                    </div>
                                                    <div>
                                                        <label
                                                            class="block text-sm font-medium text-gray-700 mb-1">House
                                                            Nr</label>
                                                        <input type="text" x-model="stepByStep.sender.houseNr"
                                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                                    </div>
                                                </div>
                                                <div class="grid grid-cols-2 gap-4">
                                                    <div>
                                                        <label
                                                            class="block text-sm font-medium text-gray-700 mb-1">ZIP</label>
                                                        <input type="text" x-model="stepByStep.sender.zip"
                                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                                    </div>
                                                    <div>
                                                        <label
                                                            class="block text-sm font-medium text-gray-700 mb-1">City</label>
                                                        <input type="text" x-model="stepByStep.sender.city"
                                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mt-6 flex justify-end">
                                            <button type="submit" :disabled="loading || !!currentCard"
                                                class="bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 text-white px-4 py-2 rounded-lg transition-colors">
                                                <i class="fas fa-plus mr-2"></i>Create Postcard
                                            </button>
                                        </div>
                                    </form>

                                    <!-- Current Card Display -->
                                    <div x-show="currentCard"
                                        class="mt-4 p-4 bg-green-50 border border-green-200 rounded-lg">
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <p class="font-medium text-green-800">Postcard Created!</p>
                                                <p class="text-sm text-green-600">Card Key: <span
                                                        x-text="currentCard"></span></p>
                                            </div>
                                            <button @click="getPostcardState()"
                                                class="text-green-600 hover:text-green-800">
                                                <i class="fas fa-sync-alt"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Additional Steps would go here following similar pattern -->
                        <!-- Step 2: Upload Image, Step 3: Add Text, Step 4: Preview & Approve -->
                        <!-- (Content truncated for brevity but would follow similar structure) -->
                    </div>
                </div>

                <!-- Validation Tab -->
                <div x-show="activeTab === 'validation'" x-cloak>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        <!-- Address Validation -->
                        <div class="bg-white rounded-lg shadow-sm border p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">
                                <i class="fas fa-map-marker-alt text-green-600 mr-2"></i>
                                Address Validation
                            </h3>
                            <form @submit.prevent="validateAddress()">
                                <div class="space-y-4">
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">First
                                                Name</label>
                                            <input type="text" x-model="validation.address.firstname"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Last
                                                Name</label>
                                            <input type="text" x-model="validation.address.lastname"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-3 gap-4">
                                        <div class="col-span-2">
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Street</label>
                                            <input type="text" x-model="validation.address.street"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">House Nr</label>
                                            <input type="text" x-model="validation.address.houseNr"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-3 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">ZIP</label>
                                            <input type="text" x-model="validation.address.zip"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">City</label>
                                            <input type="text" x-model="validation.address.city"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Country</label>
                                            <select x-model="validation.address.country"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                                <option value="CH">Switzerland</option>
                                                <option value="DE">Germany</option>
                                                <option value="AT">Austria</option>
                                                <option value="FR">France</option>
                                                <option value="IT">Italy</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Address Type</label>
                                        <select x-model="validation.addressType"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                            <option value="recipient">Recipient</option>
                                            <option value="sender">Sender</option>
                                        </select>
                                    </div>
                                </div>
                                <button type="submit"
                                    class="mt-4 w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition-colors">
                                    <i class="fas fa-check mr-2"></i>Validate Address
                                </button>
                            </form>
                        </div>

                        <!-- Text Validation -->
                        <div class="bg-white rounded-lg shadow-sm border p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">
                                <i class="fas fa-font text-purple-600 mr-2"></i>
                                Text Validation
                            </h3>
                            <form @submit.prevent="validateText()">
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Sender Text</label>
                                        <textarea x-model="validation.text" rows="6"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                            placeholder="Enter your postcard message here..."></textarea>
                                        <p class="text-xs text-gray-500 mt-1">
                                            Characters: <span x-text="validation.text.length"></span>/2000
                                        </p>
                                    </div>
                                </div>
                                <button type="submit"
                                    class="mt-4 w-full bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg transition-colors">
                                    <i class="fas fa-spell-check mr-2"></i>Validate Text
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Validation Results -->
                    <div x-show="validationResults" x-cloak class="mt-8 bg-white rounded-lg shadow-sm border p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Validation Results</h3>
                        <div x-show="validationResults?.valid"
                            class="p-4 bg-green-50 border border-green-200 rounded-lg">
                            <div class="flex items-center">
                                <i class="fas fa-check-circle text-green-600 mr-2"></i>
                                <span class="font-medium text-green-800">Validation Passed!</span>
                            </div>
                        </div>
                        <div x-show="!validationResults?.valid && validationResults?.errors?.length"
                            class="p-4 bg-red-50 border border-red-200 rounded-lg">
                            <div class="flex items-start">
                                <i class="fas fa-exclamation-circle text-red-600 mr-2 mt-0.5"></i>
                                <div>
                                    <span class="font-medium text-red-800 block mb-2">Validation Errors:</span>
                                    <ul class="list-disc list-inside text-red-700 space-y-1">
                                        <template x-for="error in validationResults.errors" :key="error">
                                            <li x-text="error"></li>
                                        </template>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Branding Tab -->
                <div x-show="activeTab === 'branding'" x-cloak>
                    <div class="bg-white rounded-lg shadow-sm border p-6">
                        <h2 class="text-xl font-semibold text-gray-900 mb-6">
                            <i class="fas fa-paint-brush text-purple-600 mr-2"></i>
                            Branding Options
                        </h2>
                        <p class="text-gray-600 mb-6">Add branding elements to your postcard (requires existing
                            postcard)</p>

                        <div x-show="!currentCard" class="p-4 bg-yellow-50 border border-yellow-200 rounded-lg mb-6">
                            <div class="flex items-center">
                                <i class="fas fa-info-circle text-yellow-600 mr-2"></i>
                                <span class="text-yellow-800">Create a postcard first in the "Step by Step" tab to add
                                    branding.</span>
                            </div>
                        </div>

                        <div x-show="currentCard" class="space-y-6">
                            <!-- Branding type selector and forms would go here -->
                            <!-- Similar structure to other forms -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function postcardTester() {
            return {
                activeTab: 'quick-send',
                loading: false,
                campaignStats: null,
                currentCard: null,
                alert: {
                    show: false,
                    type: 'success',
                    title: '',
                    message: '',
                    details: ''
                },
                stepOpen: {
                    create: true,
                    upload: false,
                    text: false,
                    branding: false,
                    preview: false
                },
                quickSend: {
                    recipient: {
                        firstname: 'John',
                        lastname: 'Doe',
                        street: 'Musterstrasse',
                        houseNr: '123',
                        zip: '8000',
                        city: 'Zürich',
                        country: 'CH'
                    },
                    sender_text: 'Hello from the Swiss Post Postcard API test interface!',
                    campaign_key: ''
                },
                stepByStep: {
                    recipient: {
                        firstname: '',
                        lastname: '',
                        street: '',
                        houseNr: '',
                        zip: '',
                        city: '',
                        country: 'CH'
                    },
                    sender: {
                        firstname: '',
                        lastname: '',
                        street: '',
                        houseNr: '',
                        zip: '',
                        city: ''
                    }
                },
                validation: {
                    address: {
                        firstname: 'John',
                        lastname: 'Doe',
                        street: 'Musterstrasse',
                        houseNr: '123',
                        zip: '8000',
                        city: 'Zürich',
                        country: 'CH'
                    },
                    addressType: 'recipient',
                    text: 'Hello from Switzerland!'
                },
                validationResults: null,

                async makeRequest(url, data = {}, method = 'POST') {
                    const options = {
                        method,
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    };

                    if (method !== 'GET') {
                        options.body = JSON.stringify(data);
                    }

                    try {
                        const response = await fetch(url, options);

                        // Parse response as JSON (works for both success and error responses)
                        const result = await response.json();

                        // For non-2xx responses, we still want to handle the structured error response
                        if (!response.ok && !result.success) {
                            // This is a structured error response from our API
                            // Let it fall through to the normal error handling below
                        } else if (!response.ok) {
                            // This is an unstructured error (shouldn't happen with our API)
                            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                        }

                        if (!result.success) {
                            let errorTitle = 'Request Failed';
                            let errorMessage = result.error || 'Request failed';
                            let details = '';

                            // Special handling for specific error types
                            if (result.type === 'json_parse_error') {
                                errorTitle = 'Swiss Post API Authentication Issue';
                                errorMessage = result.error || 'The API returned HTML instead of JSON';
                                details = result.details ? JSON.stringify(result.details, null, 2) : '';
                            } else if (result.type === 'config_error') {
                                errorTitle = 'Configuration Error';
                                errorMessage = result.error || 'API credentials not configured';
                                details = result.config_info ? JSON.stringify(result.config_info, null, 2) : '';
                            } else if (result.details) {
                                details = JSON.stringify(result.details, null, 2);
                            }

                            this.showAlert('error', errorTitle, errorMessage, details);
                            const error = new Error(result.error || 'Request failed');
                            error.alreadyHandled = true;
                            throw error;
                        }

                        return result;
                    } catch (error) {
                        // If no alert was shown above (for non-API errors), show a generic one
                        if (!error.alreadyHandled) {
                            this.showAlert('error', 'Request Failed', error.message);
                        }
                        throw error;
                    }
                },

                async makeFormRequest(url, formData) {
                    const options = {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: formData
                    };

                    try {
                        const response = await fetch(url, options);

                        // Check if response is OK
                        if (!response.ok) {
                            const errorText = await response.text();
                            try {
                                const errorJson = JSON.parse(errorText);
                                throw new Error(errorJson.message || errorJson.error || `HTTP ${response.status}: ${response.statusText}`);
                            } catch (parseError) {
                                throw new Error(`HTTP ${response.status}: ${response.statusText}\n${errorText.substring(0, 200)}...`);
                            }
                        }

                        const result = await response.json();

                        if (!result.success) {
                            throw new Error(result.error || 'Request failed');
                        }

                        return result;
                    } catch (error) {
                        this.showAlert('error', 'Request Failed', error.message);
                        throw error;
                    }
                },

                showAlert(type, title, message, details = '') {
                    this.alert = {
                        show: true,
                        type,
                        title,
                        message,
                        details
                    };
                },

                toggleStep(step) {
                    this.stepOpen[step] = !this.stepOpen[step];
                },

                async testRawOAuth() {
                    try {
                        this.loading = true;
                        const result = await this.makeRequest('/api/test/debug/raw-oauth', {}, 'POST');
                        this.showAlert('success', 'Raw OAuth Test Complete',
                            'Raw OAuth request completed - check details to see exact response',
                            JSON.stringify(result.data, null, 2));
                    } catch (error) {
                        console.error('Raw OAuth test failed:', error);
                    } finally {
                        this.loading = false;
                    }
                },

                async debugOAuth() {
                    try {
                        this.loading = true;
                        const result = await this.makeRequest('/api/test/debug/oauth', {}, 'POST');
                        this.showAlert('success', 'OAuth Debug Success',
                            'OAuth2 token obtained successfully',
                            JSON.stringify(result.data, null, 2));
                    } catch (error) {
                        console.error('OAuth debug failed:', error);
                    } finally {
                        this.loading = false;
                    }
                },

                async loadCampaignStats() {
                    try {
                        this.loading = true;
                        const result = await this.makeRequest('/api/test/campaign/stats', {}, 'POST');
                        this.campaignStats = result.data;
                        this.showAlert('success', 'Campaign Stats Loaded', 'Successfully retrieved campaign statistics');
                    } catch (error) {
                        console.error('Failed to load campaign stats:', error);
                        // Show the error in the UI when loading campaign stats
                        // This will help with debugging configuration issues
                    } finally {
                        this.loading = false;
                    }
                },

                async submitQuickSend() {
                    try {
                        this.loading = true;

                        const formData = new FormData();

                        // Append recipient fields individually as FormData doesn't handle nested objects well
                        Object.keys(this.quickSend.recipient).forEach(key => {
                            formData.append(`recipient[${key}]`, this.quickSend.recipient[key]);
                        });

                        formData.append('sender_text', this.quickSend.sender_text);
                        formData.append('campaign_key', this.quickSend.campaign_key);

                        // Check if image is selected
                        if (!this.$refs.quickSendImage.files[0]) {
                            throw new Error('Please select an image file');
                        }

                        formData.append('image', this.$refs.quickSendImage.files[0]);

                        const result = await this.makeFormRequest('/api/test/postcard/create-complete', formData);

                        this.currentCard = result.data.cardKey;
                        this.showAlert('success', 'Postcard Created!',
                            `Your postcard has been created with key: ${result.data.cardKey}`,
                            JSON.stringify(result.data, null, 2));
                    } catch (error) {
                        console.error('Failed to create postcard:', error);
                    } finally {
                        this.loading = false;
                    }
                },

                async createPostcard() {
                    try {
                        this.loading = true;

                        const result = await this.makeRequest('/api/test/postcard/create', {
                            recipient: this.stepByStep.recipient,
                            sender: this.stepByStep.sender
                        });

                        this.currentCard = result.data.cardKey;
                        this.showAlert('success', 'Postcard Created!',
                            `Postcard initialized with key: ${result.data.cardKey}`);
                    } catch (error) {
                        console.error('Failed to create postcard:', error);
                    } finally {
                        this.loading = false;
                    }
                },

                async validateAddress() {
                    try {
                        this.loading = true;

                        const result = await this.makeRequest('/api/test/validate/address', {
                            address: this.validation.address,
                            type: this.validation.addressType
                        });

                        this.validationResults = result;
                        const message = result.valid ? 'Address is valid!' : 'Address has validation errors';
                        this.showAlert(result.valid ? 'success' : 'error', 'Address Validation', message);
                    } catch (error) {
                        console.error('Failed to validate address:', error);
                    } finally {
                        this.loading = false;
                    }
                },

                async validateText() {
                    try {
                        this.loading = true;

                        const result = await this.makeRequest('/api/test/validate/text', {
                            text: this.validation.text
                        });

                        this.validationResults = result;
                        const message = result.valid ? 'Text is valid!' : 'Text has validation errors';
                        this.showAlert(result.valid ? 'success' : 'error', 'Text Validation', message);
                    } catch (error) {
                        console.error('Failed to validate text:', error);
                    } finally {
                        this.loading = false;
                    }
                },

                async getPostcardState() {
                    if (!this.currentCard) return;

                    try {
                        const result = await this.makeRequest('/api/test/postcard/state', {
                            card_key: this.currentCard
                        });

                        this.showAlert('success', 'Postcard State',
                            `Current state: ${result.data.state}`,
                            JSON.stringify(result.data, null, 2));
                    } catch (error) {
                        console.error('Failed to get postcard state:', error);
                    }
                },

                init() {
                    // Load campaign stats on initialization
                    this.loadCampaignStats();
                }
            };
        }
    </script>
</body>

</html>
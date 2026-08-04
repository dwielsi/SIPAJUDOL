@php
    $website = $website ?? null;
@endphp

<div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
    <div>
        <x-input-label for="opd_name" value="Nama OPD" />
        <x-text-input id="opd_name" name="opd_name" class="w-full" :value="old('opd_name', $website?->opd_name)" required />
        <x-input-error :messages="$errors->get('opd_name')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="website_name" value="Nama Website" />
        <x-text-input id="website_name" name="website_name" class="w-full" :value="old('website_name', $website?->website_name)" required />
        <x-input-error :messages="$errors->get('website_name')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="domain" value="Domain" />
        <x-text-input id="domain" name="domain" class="w-full" placeholder="contoh.kuburayakab.go.id" :value="old('domain', $website?->domain)" required />
        <x-input-error :messages="$errors->get('domain')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="subdomain" value="Subdomain" />
        <x-text-input id="subdomain" name="subdomain" class="w-full" :value="old('subdomain', $website?->subdomain)" />
        <x-input-error :messages="$errors->get('subdomain')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="ip_server" value="IP Server" />
        <x-text-input id="ip_server" name="ip_server" class="w-full" :value="old('ip_server', $website?->ip_server)" />
        <x-input-error :messages="$errors->get('ip_server')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="hosting" value="Hosting" />
        <x-text-input id="hosting" name="hosting" class="w-full" :value="old('hosting', $website?->hosting)" />
        <x-input-error :messages="$errors->get('hosting')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="cms" value="CMS" />
        <x-text-input id="cms" name="cms" class="w-full" placeholder="WordPress, Joomla, dll." :value="old('cms', $website?->cms)" />
        <x-input-error :messages="$errors->get('cms')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="cms_version" value="Versi CMS" />
        <x-text-input id="cms_version" name="cms_version" class="w-full" :value="old('cms_version', $website?->cms_version)" />
        <x-input-error :messages="$errors->get('cms_version')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="server_location" value="Lokasi Server" />
        <x-text-input id="server_location" name="server_location" class="w-full" :value="old('server_location', $website?->server_location)" />
        <x-input-error :messages="$errors->get('server_location')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="status" value="Status" />
        <select id="status" name="status" class="block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
            @foreach (['safe' => 'Aman', 'needs_review' => 'Perlu Pemeriksaan', 'flagged' => 'Terindikasi'] as $value => $label)
                <option value="{{ $value }}" @selected(old('status', $website?->status ?? 'safe') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('status')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="admin_name" value="Admin Website" />
        <x-text-input id="admin_name" name="admin_name" class="w-full" :value="old('admin_name', $website?->admin_name)" />
        <x-input-error :messages="$errors->get('admin_name')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="admin_email" value="Email Admin" />
        <x-text-input id="admin_email" name="admin_email" type="email" class="w-full" :value="old('admin_email', $website?->admin_email)" />
        <x-input-error :messages="$errors->get('admin_email')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="admin_phone" value="Nomor HP Admin" />
        <x-text-input id="admin_phone" name="admin_phone" class="w-full" :value="old('admin_phone', $website?->admin_phone)" />
        <x-input-error :messages="$errors->get('admin_phone')" class="mt-2" />
    </div>

    <div class="sm:col-span-2">
        <x-input-label for="notes" value="Catatan" />
        <textarea id="notes" name="notes" rows="3" class="block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white dark:placeholder-slate-500">{{ old('notes', $website?->notes) }}</textarea>
        <x-input-error :messages="$errors->get('notes')" class="mt-2" />
    </div>
</div>

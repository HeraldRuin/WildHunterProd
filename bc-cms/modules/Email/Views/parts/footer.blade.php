<div class="b-footer" style="margin-top: 0px;">
    <div class="b-container">
        @php $email_footer = setting_item_with_lang('email_footer') @endphp
        {!! $email_footer ? $email_footer : '© 2025' . env('APP_NAME') . __('All rights reserved') !!}
    </div>
</div>

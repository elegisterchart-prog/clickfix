@extends('layouts.app')

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">

<div class="container" style="max-width:900px; margin: 2rem auto;">
    <h2>Chat with Assistant</h2>

    <div id="chat-box" style="border:1px solid #ddd; padding:1rem; height:400px; overflow:auto; background:#fff;">
        {{-- messages will be appended here --}}
    </div>

    <form id="chat-form" style="margin-top:1rem; display:flex; gap:8px;">
        @csrf
        <input id="message-input" name="message" placeholder="Type your message" style="flex:1; padding:8px;" />
        <button type="submit" class="btn btn-primary">Send</button>
    </form>
</div>

<script>
(function(){
    const chatBox = document.getElementById('chat-box');
    const form = document.getElementById('chat-form');
    const input = document.getElementById('message-input');
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    function appendMessage(role, text){
        const el = document.createElement('div');
        el.style.marginBottom = '0.75rem';
        el.innerHTML = '<strong>'+role+':</strong> <div style="white-space:pre-wrap;">'+escapeHtml(String(text))+'</div>';
        chatBox.appendChild(el);
        chatBox.scrollTop = chatBox.scrollHeight;
    }

    function escapeHtml(unsafe) {
        return unsafe
             .replace(/&/g, "&amp;")
             .replace(/</g, "&lt;")
             .replace(/>/g, "&gt;")
             .replace(/\"/g, "&quot;")
             .replace(/'/g, "&#039;");
    }

    form.addEventListener('submit', function(e){
        e.preventDefault();
        const msg = input.value.trim();
        if (!msg) return;
        appendMessage('You', msg);
        input.value = '';

        fetch("/ai/chat/message", {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-XSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ message: msg })
        }).then(async r => {
            const text = await r.text();
            if (!r.ok) {
                throw new Error('HTTP ' + r.status + ': ' + text);
            }
            return JSON.parse(text);
        }).then(data => {
            if (data && data.reply) {
                appendMessage('Assistant', data.reply);
            } else {
                appendMessage('Assistant','(No reply)');
            }
        }).catch(err=>{
            appendMessage('Assistant','(Error: '+err.message+')');
        });
    });
})();
</script>
@endsection

document.addEventListener('DOMContentLoaded', function() {
    const useStampButtons = document.querySelectorAll('.use-stamp');
    
    useStampButtons.forEach(button => {
        button.addEventListener('click', function() {
            const stampId = this.dataset.stampId;
            useStamp(stampId);
        });
    });
});

function useStamp(stampId) {
    // ここでAjaxリクエストを送信してスタンプ使用を記録
    fetch('../api/use_stamp.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ stamp_id: stampId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('スタンプを使用しました！');
            // 必要に応じて、UI更新のロジックをここに追加
        } else {
            alert('エラーが発生しました: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('エラーが発生しました。');
    });
}
document.addEventListener('DOMContentLoaded', function() {
    const stamps = document.querySelectorAll('.stamp');
    
    stamps.forEach(stamp => {
        stamp.addEventListener('click', function() {
            const stampId = this.dataset.stampId;
            useStamp(stampId);
        });
    });
});

function useStamp(stampId) {
    fetch('../api/use_stamp.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ stamp_id: stampId })
    })
    .then(response => {
        if (!response.ok) {
            return response.text().then(text => {
                throw new Error(`HTTP error! status: ${response.status}, body: ${text}`);
            });
        }
        return response.json();
    })
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
        alert('エラーが発生しました: ' + error.message);
    });
}
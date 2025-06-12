<!DOCTYPE html>
<html lang="zh-CN">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>自动切换视频播放器</title>
  <style>
    body {
      display: flex;
      flex-direction: column;
      align-items: center;
      padding: 20px;
      font-family: sans-serif;
    }
    video {
      max-width: 100%;
      border: 1px solid #ccc;
      border-radius: 4px;
      box-shadow: 0 2px 6px rgba(0,0,0,0.2);
    }
    #loading {
      margin-top: 10px;
      color: #666;
    }
  </style>
</head>
<body>
  <h1>自动切换视频播放器</h1>
  <video id="player" controls autoplay></video>
  <div id="loading">正在加载视频…</div>

  <script>
    const apiEndpoint = 'https://api.kuleu.com/api/MP4_xiaojiejie?type=json';
    const player = document.getElementById('player');
    const loadingEl = document.getElementById('loading');

    // 加载并播放一条新视频
    async function loadNextVideo() {
      loadingEl.textContent = '正在加载视频…';
      try {
        const resp = await fetch(apiEndpoint);
        if (!resp.ok) throw new Error(`HTTP ${resp.status}`);
        const data = await resp.json();
        if (data.code !== 200 || !data.mp4_video) {
          throw new Error(data.msg || '返回数据不包含视频链接');
        }
        // 切换视频源
        player.src = data.mp4_video;
        await player.load();
        await player.play();
        loadingEl.textContent = '';
      } catch (err) {
        console.error('加载视频失败', err);
        loadingEl.textContent = '视频加载失败，请稍后重试';
      }
    }

    // 监听播放结束，自动加载下一条
    player.addEventListener('ended', loadNextVideo);

    // 页面加载后，先加载一条
    window.addEventListener('DOMContentLoaded', loadNextVideo);
  </script>
</body>
</html>

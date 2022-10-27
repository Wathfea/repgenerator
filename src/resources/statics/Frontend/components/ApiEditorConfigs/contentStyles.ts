
export const contentStyles = {
    youtube: `
      .responsive-youtube-player-container {
        display:block;
        width:100%;
        position:relative;
      }
      /* tinymce preview hack*/
      .responsive-youtube-player-container .mce-preview-object{
        display:block;
        width:100%;
        height:100%;
        position:absolute;
        top:0;
        left:0;
        border:none;
        margin:0;
      }
      .responsive-youtube-player-container .delete{
        display:block;
        z-index:11;
        position:absolute;
        top:0;
        right:0;
        width:30px;
        height:30px;
        line-height:30px;
        text-align:center;
        font-size:22px;
        background:rgba(2555,255,255,0.8);
      }
      .responsive-youtube-player-container.size-medium{
        max-width:600px;
      }
      .responsive-youtube-player-container.size-small{
        max-width:240px;
      }
      .responsive-youtube-player-container.size-full{
        max-width:100%;
      }
      .responsive-youtube-player-container.align-right{
        margin-left: auto; 
        margin-right: 0;
      }
      .responsive-youtube-player-container.align-left{
        margin-left: 0; 
        margin-right: 0;
      }
      .responsive-youtube-player-container.align-center{
        margin-left: auto; 
        margin-right: auto;
      }
      .responsive-youtube-player {
        position: relative;
        padding-bottom: 56.25%;
        height: 0;
      }
      .responsive-youtube-player iframe {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
      }
    `,
  }
export const dialogConfigs = () => {
    return {
      youtube:  {
        title: 'Youtube reszponsív videó beágyazása',
        body: {
          type: 'panel',
          items: [
            {
              type: 'input',
              name: 'link',
              label: 'Youtube videó link'
            },
            {
              type: 'selectbox',
              name: 'align',
              label: 'Videó vízszintes igazítás',
              items: [
                { value: 'left', text: 'Bal szélen' },
                { value: 'center', text: 'Középen' },
                { value: 'right', text: 'Jobb szélen' }
              ]
            },
            {
              type: 'selectbox',
              name: 'size',
              label: 'Videó alap megjelenési mérete',
              items: [
                { value: 'medium', text: 'Közepes méret' },
                { value: 'small', text: 'Kis méret' },
                { value: 'full', text: 'Teljes szélesség' }
              ]
            },
          ]
        },
        buttons: [
          {
            type: 'cancel',
            name: 'closeButton',
            text: 'Mégsem'
          },
          {
            type: 'submit',
            name: 'submitButton',
            text: 'Beillesztés',
            buttonType: 'primary'
          }
        ],
        initialData: {
          catdata: '',
        },
        onSubmit: (api) => {
          const data = api.getData();
          let idPattern = /^.*(?:(?:youtu\.be\/|v\/|vi\/|u\/\w\/|embed\/|shorts\/)|(?:(?:watch)?\?v(?:i)?=|\&v(?:i)?=))([^#\&\?]*).*/;
          let m = idPattern.exec(data.link);
          let html = '';
          if (m != null && m != undefined){
            html = `
              <div class="responsive-youtube-player-container align-${data.align} size-${data.size}">
                <div class="responsive-youtube-player">
                  <iframe src="https://www.youtube.com/embed/${m[1]}?autoplay=0&rel=0" frameborder="0" allowfullscreen="1" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture"></iframe>
                </div>
              </div>
              `;
              //<a class="delete" onclick="this.parentNode.remove();">&times;</a>
              //kényelemből hozzá lehetne adni, de meg kel vizsgálni hogy úgy adjuk hozzá hogy a valkue értékbe ne íródjon be csak a preview elembe
          }
          // @ts-ignore tinymce
          tinymce.activeEditor.execCommand('mceInsertRawHTML', false, html);
          api.close();
        }
      },
    }
  };

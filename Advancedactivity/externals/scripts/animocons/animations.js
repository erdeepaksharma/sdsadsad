/* $Id: animations.js 2017-08-11 00:00:00Z SocialEngineAddOns Copyright 2017-2021 BigStep Technologies Pvt.
 Ltd. $ */

;
(function (window, document) {

  var $ = 'id' in document ? document.id : window.$;
  // taken from mo.js demos
  function isIOSSafari() {
    var userAgent;
    userAgent = window.navigator.userAgent;
    return userAgent.match(/iPad/i) || userAgent.match(/iPhone/i);
  }
  ;

  // taken from mo.js demos
  function isTouch() {
    var isIETouch;
    isIETouch = navigator.maxTouchPoints > 0 || navigator.msMaxTouchPoints > 0;
    return [].indexOf.call(window, 'ontouchstart') >= 0 || isIETouch;
  }
  ;

  // taken from mo.js demos
  var isIOS = isIOSSafari(),
          clickHandler = isIOS || isTouch() ? 'touchstart' : 'click';

  function extend(a, b) {
    for (var key in b) {
      if (b.hasOwnProperty(key)) {
        a[key] = b[key];
      }
    }
    return a;
  }

  function Animocon(el, options) {
    this.el = el;
    this.options = extend({}, this.options);
    extend(this.options, options);
    this.timeline = new mojs.Timeline();

    for (var i = 0, len = this.options.tweens.length; i < len; ++i) {
      this.timeline.add(this.options.tweens[i]);
    }
    this.timeline.replay();
  }

  var animations = [];
  /* Animation 1 */
  animations['animation-1'] = function (el) {
    return new Animocon(el, {
      tweens: [
        // burst animation
        new mojs.Burst({
          parent: el,
          radius: {50: 150},
          count: 15,
          children: {
            fill: el.getStyle('color'),
            opacity: 0.6,
            radius: 15,
            duration: 1800,
            easing: mojs.easing.bezier(0.1, 1, 0.3, 1)
          }
        }),
        // ring animation
        new mojs.Shape({
          parent: el,
          type: 'circle',
          radius: {0: 50},
          fill: 'transparent',
          stroke: el.getStyle('color'),
          strokeWidth: {15: 0},
          opacity: 0.6,
          duration: 1000,
          easing: mojs.easing.sin.out
        })
      ]
    });
  }
  /* Animation 1 */

  /* Animation 2 */
  animations['animation-2'] = function (el) {
    return new Animocon(el, {
      tweens: [
        // burst animation
        new mojs.Burst({
          parent: el,
          count: 20,
          radius: {45: 120},
          timeline: {delay: 500},
          children: {
            fill: el.getStyle('color'),
            radius: 8,
            opacity: 0.6,
            duration: 1500,
            easing: mojs.easing.bezier(0.1, 1, 0.3, 1)
          }
        }),
        // ring animation
        new mojs.Shape({
          parent: el,
          radius: {0: 60},
          fill: 'transparent',
          stroke: el.getStyle('color'),
          strokeWidth: {35: 0},
          opacity: 0.6,
          duration: 800,
          easing: mojs.easing.ease.inout
        })
      ]
    });
  };
  /* Animation 2 */

  /* Animation 3 */
  animations['animation-3'] = function (el) {
    return new Animocon(el, {
      tweens: [
        // burst animation
        new mojs.Burst({
          parent: el,
          count: 30,
          radius: {70: 210},
          children: {
            fill: ['#988ADE', '#DE8AA0', '#8AAEDE', '#8ADEAD', '#DEC58A', '#8AD1DE'],
            opacity: 0.6,
            scale: 1,
            radius: {15: 0},
            duration: 1500,
            delay: 500,
            easing: mojs.easing.bezier(0.1, 1, 0.3, 1)
          }
        }),
        // ring animation
        new mojs.Shape({
          parent: el,
          type: 'circle',
          scale: {0: 1},
          radius: 60,
          fill: 'transparent',
          stroke: el.getStyle('color'),
          strokeWidth: {35: 0},
          opacity: 0.6,
          duration: 950,
          easing: mojs.easing.bezier(0, 1, 0.5, 1)
        })
      ]
    });
  }
  /* Animation 3 */

  /* Animation 4 */
  animations['animation-4'] = function (el) {
    return new Animocon(el, {
      tweens: [
        // burst animation
        new mojs.Burst({
          parent: el,
          count: 20,
          radius: {80: 240},
          children: {
            fill: ['#988ADE', '#DE8AA0', '#8AAEDE', '#8ADEAD', '#DEC58A', '#8AD1DE'],
            opacity: 0.6,
            radius: 30,
            direction: [-1, -1, -1, 1, -1],
            swirlSize: 'rand(10, 14)',
            duration: 1500,
            easing: mojs.easing.bezier(0.1, 1, 0.3, 1),
            isSwirl: true
          }
        }),
        // ring animation
        new mojs.Shape({
          parent: el,
          radius: 50,
          scale: {0: 1},
          fill: 'transparent',
          stroke: el.getStyle('color'),
          strokeWidth: {15: 0},
          opacity: 0.6,
          duration: 950,
          easing: mojs.easing.bezier(0, 1, 0.5, 1)
        })
      ]
    });
  };
  /* Animation 4 */

  /* Animation 5 */
  animations['animation-5'] = function (el) {
    return new Animocon(el, {
      tweens: [
        // burst animation
        new mojs.Burst({
          parent: el,
          count: 20,
          radius: {50: 130},
          angle: {0: 140, easing: mojs.easing.bezier(0.1, 1, 0.3, 1)},
          children: {
            fill: el.getStyle('color'),
            radius: 20,
            opacity: 0.6,
            duration: 1500,
            easing: mojs.easing.bezier(0.1, 1, 0.3, 1)
          }
        })
      ]
    });
  };
  /* Animation 5 */

  /* Animation 6 */
  animations['animation-6'] = function (el) {
    return new Animocon(el, {
      tweens: [
        // burst animation
        new mojs.Burst({
          parent: el,
          radius: {40: 110},
          count: 20,
          children: {
            shape: 'line',
            fill: 'white',
            radius: {12: 0},
            scale: 1,
            stroke: el.getStyle('color'),
            strokeWidth: 2,
            duration: 1500,
            easing: mojs.easing.bezier(0.1, 1, 0.3, 1)
          },
        }),
        // ring animation
        new mojs.Shape({
          parent: el,
          radius: {10: 60},
          fill: 'transparent',
          stroke: el.getStyle('color'),
          strokeWidth: {30: 0},
          duration: 1000,
          easing: mojs.easing.bezier(0.1, 1, 0.3, 1)
        }),
      ],
    });
  }
  /* Animation 6 */

  /* Animation 7 */
  animations['animation-7'] = function (el) {
    return new Animocon(el, {
      tweens: [
        // burst animation
        new mojs.Burst({
          parent: el,
          radius: {90: 150},
          count: 18,
          children: {
            fill: el.getStyle('color'),
            opacity: 0.6,
            scale: 1,
            radius: {'rand(5,20)': 0},
            swirlSize: 15,
            direction: [1, 1, -1, -1, 1, 1, -1, -1, -1],
            duration: 1200,
            delay: 200,
            easing: mojs.easing.bezier(0.1, 1, 0.3, 1),
            isSwirl: true
          }
        }),
        // ring animation
        new mojs.Shape({
          parent: el,
          radius: {30: 100},
          fill: 'transparent',
          stroke: el.getStyle('color'),
          strokeWidth: {30: 0},
          opacity: 0.6,
          duration: 1500,
          easing: mojs.easing.bezier(0.1, 1, 0.3, 1)
        }),
        new mojs.Shape({
          parent: el,
          radius: {30: 80},
          fill: 'transparent',
          stroke: el.getStyle('color'),
          strokeWidth: {20: 0},
          opacity: 0.3,
          duration: 1600,
          delay: 320,
          easing: mojs.easing.bezier(0.1, 1, 0.3, 1)
        }),
      ]
    });
  };
  /* Animation 7 */

  /* Animation 8 */
  animations['animation-8'] = function (el) {
    return new Animocon(el, {
      tweens: [
        // burst animation
        new mojs.Burst({
          parent: el,
          count: 28,
          radius: {50: 110},
          children: {
            fill: el.getStyle('color'),
            opacity: 0.6,
            radius: {'rand(5,20)': 0},
            scale: 1,
            swirlSize: 15,
            duration: 1600,
            easing: mojs.easing.bezier(0.1, 1, 0.3, 1),
            isSwirl: true
          }
        }),
        // burst animation
        new mojs.Burst({
          parent: el,
          count: 18,
          angle: {0: 10},
          radius: {140: 200},
          children: {
            fill: el.getStyle('color'),
            shape: 'line',
            opacity: 0.6,
            radius: {'rand(5,20)': 0},
            scale: 1,
            stroke: '#FF94A2',
            strokeWidth: 2,
            duration: 1800,
            delay: 300,
            easing: mojs.easing.bezier(0.1, 1, 0.3, 1)
          }
        }),
        // burst animation
        new mojs.Burst({
          parent: el,
          radius: {40: 80},
          count: 18,
          children: {
            fill: el.getStyle('color'),
            opacity: 0.6,
            radius: {'rand(5,20)': 0},
            scale: 1,
            swirlSize: 15,
            duration: 2000,
            delay: 500,
            easing: mojs.easing.bezier(0.1, 1, 0.3, 1),
            isSwirl: true
          }
        }),
        // burst animation
        new mojs.Burst({
          parent: el,
          count: 20,
          angle: {0: -10},
          radius: {90: 130},
          children: {
            fill: el.getStyle('color'),
            opacity: 0.6,
            radius: {'rand(10,20)': 0},
            scale: 1,
            duration: 3000,
            delay: 750,
            easing: mojs.easing.bezier(0.1, 1, 0.3, 1)
          }
        })
      ],
    });
  };
  /* Animation 8 */

  /* Animation 9 */
  animations['animation-9'] = function (el) {
    return new Animocon(el, {
      tweens: [
        // burst animation
        new mojs.Burst({
          parent: el,
          count: 6,
          radius: {60: 120},
          angle: 135,
          degree: 90,
          children: {
            fill: ['#988ADE', '#DE8AA0', '#8AAEDE', '#8ADEAD', '#DEC58A', '#8AD1DE'],
            scale: 1,
            radius: {20: 0},
            opacity: 1,
            duration: 1500,
            delay: 350,
            easing: mojs.easing.bezier(0.1, 1, 0.3, 1)
          }
        }),
        // burst animation
        new mojs.Burst({
          parent: el,
          count: 6,
          angle: 45,
          degree: -90,
          radius: {60: 120},
          children: {
            fill: ['#988ADE', '#DE8AA0', '#8AAEDE', '#8ADEAD', '#DEC58A', '#8AD1DE'],
            scale: 1,
            radius: {20: 0},
            opacity: 1,
            duration: 1500,
            delay: 350,
            easing: mojs.easing.bezier(0.1, 1, 0.3, 1)
          }
        }),
        // ring animation
        new mojs.Shape({
          parent: el,
          radius: {0: 70},
          fill: 'transparent',
          stroke: '#988ADE',
          strokeWidth: {35: 0},
          opacity: 0.6,
          duration: 750,
          easing: mojs.easing.bezier(0, 1, 0.5, 1)
        }),
        // ring animation
        new mojs.Shape({
          parent: el,
          radius: {0: 50},
          fill: 'transparent',
          stroke: '#988ADE',
          strokeWidth: {35: 0},
          opacity: 0.6,
          duration: 950,
          delay: 200,
          easing: mojs.easing.bezier(0, 1, 0.5, 1)
        })
      ]
    });
  };
  /* Animation 9 */

  /* Animation 10 */
  animations['animation-10'] = function (el) {
    return new Animocon(el, {
      tweens: [
        // burst animation
        new mojs.Burst({
          parent: el,
          radius: {100: 150},
          degree: 360,
          angle: 0,
          count: 60,
          children: {
            shape: 'line',
            fill: '#FF94A2',
            scale: 1,
            radius: {40: 0},
            opacity: 0.6,
            duration: 1000,
            stroke: el.getStyle('color'),
            strokeWidth: {1: 5},
            easing: mojs.easing.bezier(0.1, 1, 0.3, 1)
          }
        })
      ],

    });
  }
  /* Animation 10 */

  /* Animation 11 */
  animations['animation-11'] = function (el) {

    return new Animocon(el, {
      tweens: [
        // ring animation
        new mojs.Shape({
          parent: el,
          radius: {0: 135},
          fill: 'transparent',
          stroke: el.getStyle('color'),
          strokeWidth: {40: 0},
          opacity: 0.8,
          duration: 1200,
          delay: 50,
          easing: mojs.easing.bezier(0, 1, 0.5, 1)
        }),
        // ring animation
        new mojs.Shape({
          parent: el,
          radius: {0: 120},
          fill: 'transparent',
          stroke: el.getStyle('color'),
          strokeWidth: {30: 0},
          opacity: 0.6,
          duration: 1800,
          delay: 150,
          easing: mojs.easing.bezier(0, 1, 0.5, 1)
        })
      ]
    });
  };
  /* Animation 11 */

  /* Animation 12 */
  animations['animation-12'] = function (el) {
    return new Animocon(el, {
      tweens: [
        // burst animation
        new mojs.Burst({
          parent: el,
          count: 20,
          radius: {40: 90},
          angle: 92,
          top: '90%',
          children: {
            shape: 'line',
            fill: '#E2DF17',
            scale: 1,
            radius: {60: 0},
            stroke: '#E2DF17',
            strokeWidth: {4: 1},
            strokeLinecap: 'round',
            opacity: 0.5,
            duration: 1000,
            delay: 100,
            easing: mojs.easing.bezier(0.1, 1, 0.3, 1)
          }
        }),
        // burst animation
        new mojs.Burst({
          parent: el,
          count: 20,
          radius: {10: 40},
          angle: 182,
          top: '90%',
          children: {
            shape: 'line',
            fill: '#69E6FF',
            opacity: 0.5,
            scale: 1,
            radius: {35: 0},
            stroke: '#69E6FF',
            strokeWidth: {4: 1},
            strokeLinecap: 'round',
            duration: 900,
            delay: 100,
            easing: mojs.easing.bezier(0.1, 1, 0.3, 1)
          }
        }),
        // ring animation
        new mojs.Shape({
          parent: el,
          radius: {40: 0},
          radiusY: {20: 0},
          fill: '#FA6161',
          stroke: '#FA6161',
          strokeWidth: 1,
          opacity: 0.3,
          top: '90%',
          duration: 900,
          delay: 50,
          easing: 'bounce.out'
        }),
      ],

    });
  }
  /* Animation 12 */

  /* Animation 13 */
  animations['animation-13'] = function (el) {
    return new Animocon(el, {
      tweens: [
        // burst animation
        new mojs.Burst({
          parent: el,
          count: 6,
          degree: 0,
          radius: {0: 250},
          angle: -90,
          children: {
            top: [0, -25, -25, -50, -50, -50],
            left: [0, 25, -25, 50, -50, 0],
            shape: 'circle',
            fill: ['#988ADE', '#DE8AA0', '#8AAEDE', '#DC7A0E'],
            radius: {70: 0},
            scale: 1,
            stroke: '#988ADE',
            opacity: 0.6,
            duration: 1250,
            easing: mojs.easing.bezier(0.1, 1, 0.3, 1)
          },
        }),
        // burst animation
        new mojs.Burst({
          parent: el,
          count: 30,
          radius: {120: 90},
          degree: 120,
          angle: 30,
          children: {
            shape: 'line',
            radius: {70: 0},
            scale: 1,
            stroke: ['#988ADE', '#DE8AA0', '#8AAEDE', '#DC7A0E'],
            strokeWidth: {3: 1},
            duration: 1200,
            delay: 300,
            easing: mojs.easing.bezier(0.1, 1, 0.3, 1)
          }
        }),
      ]
    });
  }
  /* Animation 13 */

  /* Animation 14 */
  animations['animation-14'] = function (el) {
    return new Animocon(el, {
      tweens: [
        // ring animation
        new mojs.Shape({
          parent: el,
          duration: 1050,
          type: 'circle',
          radius: {0: 90},
          fill: 'transparent',
          stroke: '#988ADE',
          strokeWidth: {35: 0},
          opacity: 0.6,
          top: '45%',
          easing: mojs.easing.bezier(0, 1, 0.5, 1)
        }),
        new mojs.Shape({
          parent: el,
          duration: 700,
          delay: 100,
          type: 'circle',
          radius: {0: 20},
          fill: 'transparent',
          stroke: '#DE8AA0',
          strokeWidth: {5: 0},
          opacity: 0.6,
          x: 30,
          y: -30,
          easing: mojs.easing.sin.out
        }),
        new mojs.Shape({
          parent: el,
          duration: 500,
          delay: 100,
          type: 'circle',
          radius: {0: 40},
          fill: 'transparent',
          stroke: '#8AAEDE',
          strokeWidth: {5: 0},
          opacity: 0.9,
          x: 90,
          y: 160,
          isRunLess: true,
          easing: mojs.easing.sin.out
        }),
        new mojs.Shape({
          parent: el,
          duration: 500,
          delay: 100,
          type: 'circle',
          radius: {0: 70},
          fill: 'transparent',
          stroke: '#8ADEAD',
          strokeWidth: {5: 0},
          opacity: 0.7,
          x: 80,
          y: -100,
          easing: mojs.easing.sin.out
        }),
        new mojs.Shape({
          parent: el,
          duration: 500,
          delay: 100,
          type: 'circle',
          radius: {0: 80},
          fill: 'transparent',
          stroke: '#DEC58A',
          strokeWidth: {5: 0},
          opacity: 0.8,
          x: 140,
          y: -160,
          easing: mojs.easing.sin.out
        }),
        new mojs.Shape({
          parent: el,
          duration: 500,
          delay: 100,
          type: 'circle',
          radius: {0: 30},
          fill: 'transparent',
          stroke: '#8AD1DE',
          strokeWidth: {5: 0},
          opacity: 0.6,
          x: 240,
          y: -260,
          easing: mojs.easing.sin.out
        }),
        /*--*/
        new mojs.Shape({
          parent: el,
          duration: 500,
          delay: 100,
          type: 'circle',
          radius: {0: 90},
          fill: 'transparent',
          stroke: '#F35186',
          strokeWidth: {5: 0},
          opacity: 0.6,
          x: 190,
          y: -170,
          easing: mojs.easing.sin.out
        }),
        new mojs.Shape({
          parent: el,
          duration: 500,
          delay: 100,
          type: 'circle',
          radius: {0: 50},
          fill: 'transparent',
          stroke: '#988ADE',
          strokeWidth: {5: 0},
          opacity: 0.6,
          x: 100,
          y: -20,
          easing: mojs.easing.sin.out
        }),
        new mojs.Shape({
          parent: el,
          duration: 500,
          delay: 100,
          type: 'circle',
          radius: {0: 60},
          fill: 'transparent',
          stroke: '#DE8AA0',
          strokeWidth: {5: 0},
          opacity: 0.6,
          x: 40,
          y: -60,
          easing: mojs.easing.sin.out
        }),
        new mojs.Shape({
          parent: el,
          duration: 500,
          delay: 180,
          type: 'circle',
          radius: {0: 80},
          fill: 'transparent',
          stroke: '#8AAEDE',
          strokeWidth: {5: 0},
          opacity: 0.9,
          x: -10,
          y: -80,
          isRunLess: true,
          easing: mojs.easing.sin.out
        }),
        new mojs.Shape({
          parent: el,
          duration: 800,
          delay: 240,
          type: 'circle',
          radius: {0: 80},
          fill: 'transparent',
          stroke: '#8ADEAD',
          strokeWidth: {5: 0},
          opacity: 0.7,
          x: -70,
          y: -10,
          easing: mojs.easing.sin.out
        }),
        new mojs.Shape({
          parent: el,
          duration: 800,
          delay: 240,
          type: 'circle',
          radius: {0: 90},
          fill: 'transparent',
          stroke: '#DEC58A',
          strokeWidth: {5: 0},
          opacity: 0.8,
          x: 80,
          y: -50,
          easing: mojs.easing.sin.out
        }),
        new mojs.Shape({
          parent: el,
          duration: 1000,
          delay: 300,
          type: 'circle',
          radius: {0: 75},
          fill: 'transparent',
          stroke: '#8AD1DE',
          strokeWidth: {5: 0},
          opacity: 0.6,
          x: 20,
          y: -100,
          easing: mojs.easing.sin.out
        }),
        /*--*/
        new mojs.Shape({
          parent: el,
          duration: 500,
          delay: 100,
          type: 'circle',
          radius: {0: 90},
          fill: 'transparent',
          stroke: '#F35186',
          strokeWidth: {5: 0},
          opacity: 0.8,
          x: -40,
          y: -90,
          easing: mojs.easing.sin.out
        })
      ]
    });
  };
  /* Animation 14 */

  /* Animation 15 */
  animations['animation-15'] = function (el) {
    return new Animocon(el, {
      tweens: [
        // burst animation
        new mojs.Burst({
          parent: el,
          top: '90%',
          count: 50,
          radius: {70: 300},
          degree: 90,
          angle: -45,
          children: {
            shape: 'line',
            fill: el.getStyle('color'),
            radius: {90: 0},
            scale: 1,
            stroke: el.getStyle('color'),
            opacity: .7,
            duration: 2000,
            easing: mojs.easing.bezier(0.1, 1, 0.3, 1)
          },
        }),
      ],
    });
  };
  /* Animation 15 */

  /* Animation 16 */
  animations['animation-16'] = function (el) {
    return new Animocon(el, {
      tweens: [
        // burst animation (circles)
        new mojs.Burst({
          parent: el,
          count: 40,
          radius: {0: 280},
          degree: 120,
          angle: -60,
          opacity: 0.8,
          children: {
            // fill: '#FF6767',
            stroke: ['#BF62A6', '#F28C33', '#F5D63D', '#79C267', '#78C5D6'],
            scale: 2,
            radius: {'rand(15,20)': 0},
            duration: 1700,
            delay: 350,
            easing: mojs.easing.bezier(0.1, 1, 0.3, 1)
          }
        }),
        new mojs.Burst({
          parent: el,
          count: 10,
          degree: 0,
          radius: {80: 250},
          angle: 180,
          children: {
            top: [45, 0, 45],
            left: [-15, 0, 15],
            shape: 'line',
            radius: {60: 0},
            scale: 1,
            // stroke: '#FF6767',
            stroke: ['#BF62A6', '#F28C33', '#F5D63D', '#79C267', '#78C5D6'],
            opacity: 0.7,
            duration: 950,
            delay: 200,
            easing: mojs.easing.bezier(0.1, 1, 0.3, 1)
          },
        }),
      ],
    });
  };
  /* Animation 16 */

  /* Animation 17 */
  animations['animation-17'] = function (el) {
    return new Animocon(el, {
      tweens: [
        // burst animation (line1)
        new mojs.Burst({
          parent: el,
          left: '65%', top: '40%',
          count: 50,
          radius: {80: 240},
          angle: -90,
          degree: 180,
          children: {
            shape: 'line',
            scale: 1,
            radius: {40: 0},
            stroke: ['#BF62A6', '#F28C33', '#F5D63D', '#79C267', '#78C5D6'],
            duration: 600,
            easing: mojs.easing.bezier(0.1, 1, 0.3, 1)
          },
        }),
        // burst animation (circles)
        new mojs.Burst({
          parent: el,
          left: '65%', top: '40%',
          count: 20,
          radius: {40: 100},
          degree: 180,
          angle: -88,
          opacity: 0.6,
          children: {
            fill: ['#BF62A6', '#F28C33', '#F5D63D', '#79C267', '#78C5D6'],
            scale: 1,
            radius: {'rand(5,20)': 0},
            isSwirl: true,
            swirlSize: 4,
            duration: 1600,
            delay: [0, 350, 200, 150, 400],
            easing: mojs.easing.bezier(0.1, 1, 0.3, 1)
          }
        }),
      ],
    });
  }
  /* Animation 17 */

  /* Animation 18 */
  animations['animation-18'] = function (el) {
    el.setStyles({
      height: '300px',
      width: '300px'
    });
    return new Animocon(el, {
      tweens: [
        new mojs.Burst({
          parent: el,
          count: 6,
          left: '0%',
          top: '-50%',
          radius: {0: 60},
          children: {
            radius: {20: 20},
            fill: ['#988ADE', '#DE8AA0', '#8AAEDE', '#8ADEAD', '#DEC58A', '#8AD1DE'],
            duration: 2000,
            easing: mojs.easing.bezier(0.1, 1, 0.3, 1)
          }
        }),
        new mojs.Burst({
          parent: el,
          left: '-100%',
          top: '-20%',
          count: 14,
          radius: {0: 120},
          children: {
            radius: {20: 20},
            fill: ['#988ADE', '#DE8AA0', '#8AAEDE', '#8ADEAD', '#DEC58A', '#8AD1DE'],
            duration: 1600,
            delay: 100,
            easing: mojs.easing.bezier(0.1, 1, 0.3, 1)
          }
        }),
        new mojs.Burst({
          parent: el,
          left: '130%',
          top: '-70%',
          count: 8,
          radius: {0: 90},
          children: {
            radius: {20: 20},
            fill: ['#988ADE', '#DE8AA0', '#8AAEDE', '#8ADEAD', '#DEC58A', '#8AD1DE'],
            duration: 1500,
            delay: 200,
            easing: mojs.easing.bezier(0.1, 1, 0.3, 1)
          }
        }),
        new mojs.Burst({
          parent: el,
          left: '-20%',
          top: '-150%',
          count: 14,
          radius: {0: 60},
          children: {
            radius: {20: 20},
            fill: ['#988ADE', '#DE8AA0', '#8AAEDE', '#8ADEAD', '#DEC58A', '#8AD1DE'],
            duration: 2000,
            delay: 400,
            easing: mojs.easing.bezier(0.1, 1, 0.3, 1)
          }
        }),
        new mojs.Burst({
          parent: el,
          left: '-56%', top: '-60%',
          count: 14,
          radius: {0: 120},
          children: {
            radius: {20: 20},
            fill: ['#988ADE', '#DE8AA0', '#8AAEDE', '#8ADEAD', '#DEC58A', '#8AD1DE'],
            duration: 1600,
            delay: 400,
            easing: mojs.easing.bezier(0.1, 1, 0.3, 1)
          }
        }),
        new mojs.Burst({
          parent: el,
          count: 12,
          left: '30%', top: '-100%',
          radius: {0: 60},
          children: {
            radius: {20: 20},
            fill: ['#988ADE', '#DE8AA0', '#8AAEDE', '#8ADEAD', '#DEC58A', '#8AD1DE'],
            duration: 1400,
            delay: 400,
            easing: mojs.easing.bezier(0.1, 1, 0.3, 1)
          }
        }),
        new mojs.Burst({
          parent: el,
          count: 10,
          left: '20%',
          top: '-80%',
          radius: {0: 60},
          children: {
            radius: {20: 20},
            fill: ['#988ADE', '#DE8AA0', '#8AAEDE', '#8ADEAD', '#DEC58A', '#8AD1DE'],
            duration: 2000,
            delay: 600,
            easing: mojs.easing.bezier(0.1, 1, 0.3, 1)
          }
        }),
        new mojs.Burst({
          parent: el,
          count: 10,
          left: '50%',
          top: '-90%',
          radius: {0: 60},
          children: {
            radius: {20: 20},
            fill: ['#988ADE', '#DE8AA0', '#8AAEDE', '#8ADEAD', '#DEC58A', '#8AD1DE'],
            duration: 2000,
            delay: 500,
            easing: mojs.easing.bezier(0.1, 1, 0.3, 1)
          }
        }),
        new mojs.Burst({
          parent: el,
          count: 10,
          left: '90%',
          top: '-60%',
          radius: {0: 60},
          children: {
            radius: {20: 20},
            fill: ['#988ADE', '#DE8AA0', '#8AAEDE', '#8ADEAD', '#DEC58A', '#8AD1DE'],
            duration: 2000,
            delay: 500,
            easing: mojs.easing.bezier(0.1, 1, 0.3, 1)
          }
        })

      ],
    });
  };
  /* Animation 18 */

  /* Animation Background */
  animations['animation-background'] = function (el, background) {
    el.style = '';
    el.empty();
    var path = en4.core.staticBaseUrl + 'application/modules/Advancedactivity/externals/images/animations/';
    var span = new Element('span', {
      class: 'animation',
      styles: {
        bottom: '0px',
        backgroundImage: 'url(' + path + background.replace('background-', '') + '.gif)'
      }

    }).inject(el);
    setTimeout(function () {
      span.addClass('animation_active');
    }, 2);

  };
  /* Animation Background */

  var animationPlay = {
    element: null,
    curentTarget: null,
    playAudio: false,
    audioEl: null,
    count: 0,
    loadAudio: function () {
      this.audioEl = new Element('audio', {
        preload: 'auto',
        style: {
          display: 'none'
        }
      }).inject(document.body);
      new Element('source', {
        'src': en4.core.staticBaseUrl + 'application/modules/Advancedactivity/externals/scripts/animocons/beep.mp3',
        type: 'audio/mpeg'
      }).inject(this.audioEl);
    },
    audio: function () {
      if (!this.playAudio) {
        return;
      }
      if (this.audioEl) {
        this.audioEl.destroy();
      }
      this.loadAudio();
      this.audioEl.play();
    },
    attach: function (event) {

      if (!event || !event.target || event.target === this.curentTarget || !$(event.target).get('data-animation')) {
        return;
      }
      event.stop();
      this.stop();
      this.start(event);
      this.curentTarget = event.target;
    },
    start: function (event) {
      $(document.body).setStyle('position', 'relative');

      if (!this.element) {
        this.element = new Element('div', {
          'class': 'animations-wapper'
        }).inject($(document.body));
        this.element.addEvent(clickHandler, function (e) {
          e.stop();
          this.stop();
        }.bind(this));
      }
      this.audio();
      this.element.style = '';
      this.element.setStyles({
        left: event.page.x,
        top: event.page.y,
        position: 'absolute',
        zIndex: 1000,
        color: $(event.target).getStyle('color'),
      });
      var i = $(event.target).get('data-animation');
      var fn;
      if (i.indexOf('background-') > -1) {
        fn = animations['animation-background'];
        $try(fn(this.element, i));
      } else {
        fn = animations['animation-' + i];
        $try(fn(this.element));
      }

      this.count++;
      setTimeout(this.stopIfCompleted.bind(animationPlay), 2000);
    },
    stopIfCompleted: function () {
      this.count--;
      if (this.count == 0) {
        this.stop();
      }
    },
    stop: function () {
      if (this.audioEl) {
        this.audioEl.destroy();
      }
      if (!this.element) {
        return;
      }

      this.element.style = '';
      this.curentTarget = null;
      this.element.empty();
    }
  };
  en4.core.runonce.add(function () {
    $('global_wrapper').addEvent(clickHandler, animationPlay.attach.bind(animationPlay));
  });

})(window, document); // END NAMESPACE
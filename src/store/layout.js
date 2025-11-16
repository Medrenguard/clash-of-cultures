const layout = () => {
  return {
    city: [
      {
        id: 1,
        destroyed: false,
        onEventCard: false, // поселение находится на карточке события
        region: 10,
        tile: 1,
        mood: 'happy', // happy, neutral, angry
        owner: 'player' // player, AI, rebels
      },
      {
        id: 2,
        destroyed: false,
        region: 1,
        tile: 4,
        mood: 'happy', // happy, neutral, angry
        owner: 'AI' // player, AI, rebels
      }
    ],
    buildings: [
      {
        id: 1,
        cityId: 2,
        type: 'temple',
        destroyed: false,
        onEventCard: false, // здание находится на карточке события
        owner: 'AI' // player, AI, rebels ; TODO: переопределить
      },
      {
        id: 2,
        cityId: 2,
        type: 'academy',
        destroyed: false,
        onEventCard: false, // здание находится на карточке события
        owner: 'AI' // player, AI, rebels ; TODO: переопределить
      },
      {
        id: 3,
        cityId: 2,
        type: 'castle',
        destroyed: false,
        onEventCard: false, // здание находится на карточке события
        owner: 'AI' // player, AI, rebels ; TODO: переопределить
      },
      {
        id: 4,
        cityId: 2,
        type: 'port',
        destroyed: false,
        onEventCard: false, // здание находится на карточке события
        owner: 'AI' // player, AI, rebels ; TODO: переопределить
      },
      {
        id: 5,
        cityId: 1,
        type: 'temple',
        destroyed: false,
        onEventCard: false, // здание находится на карточке события
        owner: 'player' // player, AI, rebels ; TODO: переопределить
      },
      {
        id: 6,
        cityId: 1,
        type: 'academy',
        destroyed: false,
        onEventCard: false, // здание находится на карточке события
        owner: 'player' // player, AI, rebels ; TODO: переопределить
      },
      {
        id: 7,
        cityId: 1,
        type: 'castle',
        destroyed: false,
        onEventCard: false, // здание находится на карточке события
        owner: 'player' // player, AI, rebels ; TODO: переопределить
      },
      {
        id: 8,
        cityId: 1,
        type: 'port',
        destroyed: false,
        onEventCard: false, // здание находится на карточке события
        owner: 'player' // player, AI, rebels ; TODO: переопределить
      }
    ],
    units: [
      // {
      //   id: 1,
      //   type: 'settler',
      //   alive: true,
      //   died_in_battle: false,
      //   founded_the_city: false,
      //   region: 8,
      //   tile: 3,
      //   owner: 'player',
      //   selected: false,
      //   canMove_onThisAction: true,
      //   canMove_onThisRound: true
      // },
      // {
      //   id: 2,
      //   type: 'settler',
      //   alive: true,
      //   died_in_battle: false,
      //   founded_the_city: false,
      //   region: 8,
      //   tile: 3,
      //   owner: 'player',
      //   selected: false,
      //   canMove_onThisAction: true,
      //   canMove_onThisRound: true
      // }
      // {
      //   id: 3,
      //   type: 'settler',
      //   alive: true,
      //   died_in_battle: false,
      //   founded_the_city: false,
      //   region: 1,
      //   tile: 4,
      //   owner: 'AI',
      //   selected: false,
      //   canMove_onThisAction: true,
      //   canMove_onThisRound: true
      // },
      // {
      //   id: 4,
      //   type: 'infantry',
      //   alive: true,
      //   region: 10,
      //   tile: 1,
      //   owner: 'player',
      //   selected: false,
      //   canMove_onThisAction: true,
      //   canMove_onThisRound: true,
      //   canAttack: true
      // },
      // {
      //   id: 5,
      //   type: 'ship',
      //   alive: true,
      //   region: 8,
      //   tile: 3,
      //   owner: 'player',
      //   selected: false,
      //   canMove_onThisAction: true,
      //   canMove_onThisRound: true,
      //   canAttack: true
      // },
      // {
      //   id: 10,
      //   type: 'ship',
      //   alive: true,
      //   region: 8,
      //   tile: 3,
      //   owner: 'player',
      //   selected: false,
      //   canMove_onThisAction: true,
      //   canMove_onThisRound: true,
      //   canAttack: true
      // },
      // {
      //   id: 11,
      //   type: 'ship',
      //   alive: true,
      //   region: 8,
      //   tile: 3,
      //   owner: 'player',
      //   selected: false,
      //   canMove_onThisAction: true,
      //   canMove_onThisRound: true,
      //   canAttack: true
      // },
      // {
      //   id: 12,
      //   type: 'ship',
      //   alive: true,
      //   region: 8,
      //   tile: 3,
      //   owner: 'player',
      //   selected: false,
      //   canMove_onThisAction: true,
      //   canMove_onThisRound: true,
      //   canAttack: true
      // },
      // {
      //   id: 9,
      //   type: 'infantry',
      //   alive: true,
      //   region: 10,
      //   tile: 2,
      //   owner: 'player',
      //   selected: false,
      //   canMove_onThisAction: true,
      //   canMove_onThisRound: true,
      //   canAttack: true
      // },
      // {
      //   id: 6,
      //   type: 'infantry',
      //   alive: true,
      //   region: 1,
      //   tile: 4,
      //   owner: 'AI',
      //   selected: false,
      //   canMove_onThisAction: true,
      //   canMove_onThisRound: true,
      //   canAttack: true
      // }
      // {
      //   id: 7,
      //   type: 'ship',
      //   alive: true,
      //   region: 10,
      //   tile: 4,
      //   owner: 'player',
      //   selected: false,
      //   canMove_onThisAction: true,
      //   canMove_onThisRound: true,
      //   canAttack: true
      // },
      // {
      //   id: 8,
      //   type: 'ship',
      //   alive: true,
      //   region: 1,
      //   tile: 4,
      //   owner: 'AI',
      //   selected: false,
      //   canMove_onThisAction: true,
      //   canMove_onThisRound: true,
      //   canAttack: true
      // }
    ]
  }
}

export default {
  namespaced: true,
  state: layout
}

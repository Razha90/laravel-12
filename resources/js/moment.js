import moment from 'moment';
import 'moment/locale/id';
import 'moment/locale/en-gb';

const locale = document.documentElement.lang || 'id';

moment.updateLocale('id', {
    relativeTime: {
        future: "dalam %s",
        past: "%s yang lalu",
        s: "Beberapa Detik",
        m: "Semenit",
        mm: "%d Menit",
        h: "Sejam",
        hh: "%d Jam",
        d: "Sehari",
        dd: "%d Hari",
        M: "Sebulan",
        MM: "%d bulan",
        y: "setahun",
        yy: "%d tahun"
    }
});

moment.updateLocale('en', {
  relativeTime: {
      future: "in %s",
      past: "%s ago",
      s: "a few seconds",
      m: "a minute",
      mm: "%d minutes",
      h: "an hour",
      hh: "%d hours",
      d: "a day",
      dd: "%d days",
      M: "a month",
      MM: "%d months",
      y: "a year",
      yy: "%d years"
  }
});

// Konfigurasi bahasa Prancis
moment.updateLocale('fr', {
  relativeTime: {
      future: "dans %s",
      past: "il y a %s",
      s: "quelques secondes",
      m: "une minute",
      mm: "%d minutes",
      h: "une heure",
      hh: "%d heures",
      d: "un jour",
      dd: "%d jours",
      M: "un mois",
      MM: "%d mois",
      y: "un an",
      yy: "%d ans"
  }
});

moment.locale(locale);

window.moment = moment;

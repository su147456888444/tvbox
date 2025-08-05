# -*- coding: utf-8 -*-
# @Author  : Doubebly
# @Time    : 2025/8/5 09:21


import sys
import requests
import re
sys.path.append('..')
from base.spider import Spider


class Spider(Spider):
    def getName(self):
        return self.tv.name

    def init(self, extend):
        self.tv = TvLive()
        self.cache = {}
        pass

    def getDependence(self):
        return []

    def isVideoFormat(self, url):
        pass

    def manualVideoCheck(self):
        pass

    def liveContent(self, url):
        return self.tv.get_tv_list(self.getProxyUrl())

    def homeContent(self, filter):
        return {}

    def homeVideoContent(self):
        return {}

    def categoryContent(self, cid, page, filter, ext):
        return {}

    def detailContent(self, did):
        return {}

    def searchContent(self, key, quick, page='1'):
        return {}

    def searchContentPage(self, keywords, quick, page):
        return {}

    def playerContent(self, flag, pid, vipFlags):
        return {}

    def localProxy(self, params):
        pid = params['pid']
        play_data = self.cache.get(pid, None)
        if play_data is None:
            play_data = self.tv.get_info(params)
            self.cache['pid'] = play_data
        # return self.tv.get_info(params)
        return play_data

    def destroy(self):
        return '正在Destroy'


class TvLive:
    def __init__(self):
        self.headers = {
            "User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36"
        }
        self.d = [
            {'pid': 'PenthouseBLACK', 'img': 'https://freeshot.live/upload/source/Penthouse-Black-logo.png',
             'name': 'Penthouse Black'},
            {'pid': 'Extasy4K', 'img': 'https://freeshot.live/upload/source/Extasy4K-logo.png', 'name': 'EXTASY 4K'},
            {'pid': 'VIXEN', 'img': 'https://freeshot.live/upload/source/VIXEN-logo.png', 'name': 'VIXEN'},
            {'pid': 'Penthouse', 'img': 'https://freeshot.live/upload/source/Penthouse-logo.png', 'name': 'Penthouse'},
            {'pid': 'HOT-HD', 'img': 'https://freeshot.live/upload/source/HOT-HD-logo.png', 'name': 'HOT HD'},
            {'pid': 'PinkoClubTV', 'img': 'https://freeshot.live/upload/source/PinkoClubTV-logo.png',
             'name': 'Pinko Club TV'},
            {'pid': 'NuartTV', 'img': 'https://freeshot.live/upload/source/NuartTV-logo.png', 'name': 'Nuart TV'},
            {'pid': 'DesireTV', 'img': 'https://freeshot.live/upload/source/DesireTV-logo.png', 'name': 'Desire TV'},
            {'pid': 'Tiny4k3', 'img': 'https://freeshot.live/upload/source/Adult-Tiny-4k-logo.png',
             'name': 'Adult Tiny 4K III'},
            {'pid': 'Tiny4k2', 'img': 'https://freeshot.live/upload/source/Adult-Tiny-4k-logo.png',
             'name': 'Adult Tiny 4K II'},
            {'pid': 'Tiny4k1', 'img': 'https://freeshot.live/upload/source/Adult-Tiny-4k-logo.png',
             'name': 'Adult Tiny 4K'},
            {'pid': 'BalkanErotic', 'img': 'https://freeshot.live/upload/source/BalkanErotic-logo.png',
             'name': 'Balkan Erotic'},
            {'pid': 'EroLuxeShemales', 'img': 'https://freeshot.live/upload/source/EroLuxe-Shemales-logo.png',
             'name': 'EroLuxe Shemales'},
            {'pid': 'Mofos', 'img': 'https://freeshot.live/upload/source/MOFOS-logo.png', 'name': 'Mofos'},
            {'pid': 'cum4k', 'img': 'https://freeshot.live/upload/source/CUM-4K-logo.png', 'name': 'CUM 4K'},
            {'pid': 'XXL', 'img': 'https://freeshot.live/upload/source/XXL-logo.png', 'name': 'XXL'},
            {'pid': '4kporn4', 'img': 'https://freeshot.live/upload/source/4K-PORN-LOVE-logo.png',
             'name': '4K PORN LOVE IV'},
            {'pid': '4kporn3', 'img': 'https://freeshot.live/upload/source/4K-PORN-LOVE-logo.png',
             'name': '4K PORN LOVE III'},
            {'pid': '4kporn2', 'img': 'https://freeshot.live/upload/source/4K-PORN-LOVE-logo.png',
             'name': '4K PORN LOVE II'},
            {'pid': '4kporn', 'img': 'https://freeshot.live/upload/source/4K-PORN-LOVE-logo.png',
             'name': '4K PORN LOVE'},
            {'pid': 'SecretCircleTV', 'img': 'https://freeshot.live/upload/source/Secret-Cercle-TV-logo.png',
             'name': 'Secret Circle TV'},
            {'pid': 'HOTXXL', 'img': 'https://freeshot.live/upload/source/HOT-XXL-logo.png', 'name': 'HOT XXL'},
            {'pid': 'EvilAngel', 'img': 'https://freeshot.live/upload/source/Evil-Angel-logo.png',
             'name': 'Evil Angel'},
            {'pid': 'TransErotica', 'img': 'https://freeshot.live/upload/source/TRANSEROTICA-logo.png',
             'name': 'Trans Erotica'},
            {'pid': 'HOTMan', 'img': 'https://freeshot.live/upload/source/HOT-Man-logo.png', 'name': 'HOT Man'},
            {'pid': 'ExxxoticaTV', 'img': 'https://freeshot.live/upload/source/EXXXotica-logo.png',
             'name': 'EXXXotica'},
            {'pid': 'SexPriveEurope', 'img': 'https://freeshot.live/upload/source/sexprive-europe-logo.png',
             'name': 'SexPrivé Europe'},
            {'pid': 'Television-X', 'img': 'https://freeshot.live/upload/source/Television-X-logo.png',
             'name': 'Television X'},
            {'pid': 'Barely-Legal-TV', 'img': 'https://freeshot.live/upload/source/Barely-Legal-TV-logo.png',
             'name': 'Barely Legal TV'},
            {'pid': 'SeXation', 'img': 'https://freeshot.live/upload/source/SeXation-logo.png', 'name': 'SeXation'},
            {'pid': 'LeoGoldTV', 'img': 'https://freeshot.live/upload/source/Leo-TV-Gold-logo.png',
             'name': 'Leo Gold TV'},
            {'pid': 'HotPleasure', 'img': 'https://freeshot.live/upload/source/HotPleasure-logo.png',
             'name': 'Hot Pleasure'},
            {'pid': 'LeoTV', 'img': 'https://freeshot.live/upload/source/Leo-TV-logo.png', 'name': 'Leo TV'},
            {'pid': 'PenthouseNN', 'img': 'https://freeshot.live/upload/source/Penthouse-Naughty-Nights-logo.png',
             'name': 'Penthouse Naughty Nights'},
            {'pid': 'PureBabes', 'img': 'https://freeshot.live/upload/source/Pure-Babes-logo.png',
             'name': 'Pure Babes'},
            {'pid': 'ExtasyHD', 'img': 'https://freeshot.live/upload/source/EXTASY-TV-logo.png', 'name': 'EXTASY TV'},
            {'pid': 'True-Amateurs', 'img': 'https://freeshot.live/upload/source/True-Amateurs-logo.png',
             'name': 'True Amateurs'},
            {'pid': 'PenthouseAFM', 'img': 'https://freeshot.live/upload/source/Penthouse-After-Midnight-logo.png',
             'name': 'Penthouse After Midnight'},
            {'pid': 'VividTV', 'img': 'https://freeshot.live/upload/source/Vivid-TV-logo.png', 'name': 'Vivid TV'},
            {'pid': 'PenthouseTV', 'img': 'https://freeshot.live/upload/source/PenthouseTV-logo.png',
             'name': 'Penthouse TV'},
            {'pid': 'PassionXXX', 'img': 'https://freeshot.live/upload/source/PassionXXX-logo.png',
             'name': 'Passion XXX'},
            {'pid': 'Venus', 'img': 'https://freeshot.live/upload/source/VenusPorn-logo.png', 'name': 'Venus'},
            {'pid': 'DorcelXXX', 'img': 'https://freeshot.live/upload/source/DorcelXXX-logo.png',
             'name': 'Dorcel XXX'},
            {'pid': 'CentoXCento', 'img': 'https://freeshot.live/upload/source/CentoXCento-logo.png',
             'name': 'Cento X Cento'},
            {'pid': 'SuperONE', 'img': 'https://freeshot.live/upload/source/SuperOne-logo.png', 'name': 'Super ONE'},
            {'pid': 'TransAngels', 'img': 'https://freeshot.live/upload/source/TransAngels.png',
             'name': 'Trans Angels'},
            {'pid': 'SexyHOT', 'img': 'https://freeshot.live/upload/source/sexyhot-logo.png', 'name': 'Sexy Hot'},
            {'pid': 'HustlerTV', 'img': 'https://freeshot.live/upload/source/HustlerTV-logo.png',
             'name': 'Hustler TV'},
            {'pid': 'DorcelTVAfrica', 'img': 'https://freeshot.live/upload/source/Dorcel-TV-Africa-logo.png',
             'name': 'Dorcel TV Africa'},
            {'pid': 'SextremeTV', 'img': 'https://freeshot.live/upload/source/Sextreme-logo.png', 'name': 'Sextreme'},
            {'pid': 'MeidenvanHolland', 'img': 'https://freeshot.live/upload/source/MeidenvanHolland-logo.png',
             'name': 'Meiden van Holland'},
            {'pid': 'PlayboyTV', 'img': 'https://freeshot.live/upload/source/PlayBoyTV-logo.png',
             'name': 'Playboy TV'},
            {'pid': 'HOTTaboo', 'img': 'https://freeshot.live/upload/source/HOTTaboo-logo.png', 'name': 'HOT Taboo'},
            {'pid': 'PenthousePassion', 'img': 'https://freeshot.live/upload/source/PenthousePassion-logo.png',
             'name': 'Penthouse Passion'},
            {'pid': 'Babes', 'img': 'https://freeshot.live/upload/source/babes-tv-porn-logo.png',
             'name': 'Babes TV HD'},
            {'pid': 'BangU', 'img': 'https://freeshot.live/upload/source/BangU-logo.png', 'name': 'Bang U'},
            {'pid': 'Tiny4K', 'img': 'https://freeshot.live/upload/source/Tiny_4K_logo.png', 'name': 'Tiny 4K'},
            {'pid': 'FAKETAXI', 'img': 'https://freeshot.live/upload/source/FAKETAXI-logo.png', 'name': 'FAKE TAXI'},
            {'pid': 'BlueHustler', 'img': 'https://freeshot.live/upload/source/Blue_Hustler_logo.png',
             'name': 'Blue Hustler'},
            {'pid': 'PenthouseRealityTV', 'img': 'https://freeshot.live/upload/source/PenthouseRealityTV-logo.png',
             'name': 'Penthouse Reality TV'},
            {'pid': 'PenthouseGold', 'img': 'https://freeshot.live/upload/source/Penthouse_Gold_logo.png',
             'name': 'Penthouse Gold'},
            {'pid': 'XyPlus', 'img': 'https://freeshot.live/upload/source/XYPLUS-logo.png', 'name': 'XY PLUS'},
            {'pid': 'HOT', 'img': 'https://freeshot.live/upload/source/Canal-HOT-logo.png', 'name': 'HOT'},
            {'pid': 'EroX-XxX', 'img': 'https://freeshot.live/upload/source/erox-eroxxx-logo.png',
             'name': 'EroXXX HD'},
            {'pid': 'RedlightHD', 'img': 'https://freeshot.live/upload/source/Redlight_HD-logo.png',
             'name': 'REDLIGHT HD'},
            {'pid': 'BrazzersTVEU', 'img': 'https://freeshot.live/upload/source/Brazzers_TV_Europe-logo.png',
             'name': 'Brazzers TV Europe'},
            {'pid': 'Private', 'img': 'https://freeshot.live/upload/source/PrivateTV-porn-logo.png',
             'name': 'Private TV'},
            {'pid': 'VividHD', 'img': 'https://freeshot.live/upload/source/VIVIDRED-logo.png', 'name': 'Vivid RED HD'},
            {'pid': 'Dusk', 'img': 'https://freeshot.live/upload/source/Dusk-tv-logo.png', 'name': 'Dusk TV'},
            {'pid': 'RealityKingsTV', 'img': 'https://freeshot.live/upload/source/Reality-Kings-TV-logo.png',
             'name': 'Reality Kings TV'},
            {'pid': 'DorcelTV', 'img': 'https://freeshot.live/upload/source/Dorcel-TV-logo.png', 'name': 'Dorcel TV'},
            {'pid': 'SexPrive', 'img': 'https://freeshot.live/upload/source/sexprive-logo.png', 'name': 'SexPrivé'},
            {'pid': 'HustlerHD', 'img': 'https://freeshot.live/upload/source/Hustler-logo.png', 'name': 'Hustler HD'}
        ]
        self.name = 'FreesHot'

    def get_tv_list(self, host):
        tv_list = self.d
        d = ['#EXTM3U']
        for i in tv_list:
            pid = i['pid']
            img = i['img']
            name = i['name']
            d.append(f"#EXTINF:-1 tvg-id=\"{pid}\" tvg-name=\"{name}\" tvg-logo=\"{img}\" group-title=\"{self.name}\",{name}")
            d.append(f'{host}&pid={pid}')
        return '\n'.join(d)

    def get_info(self, params):
        pid = params['pid']
        res = requests.get(f'https://popcdn.day/play.php?stream={pid}', headers=self.headers)
        token = re.findall('token=(.*?)&remote', res.text)
        if len(token) == 0:
            return [302, "text/plain", None, {
                'Location': 'https://sf1-cdn-tos.huoshanstatic.com/obj/media-fe/xgplayer_doc_video/mp4/xgplayer-demo-720p.mp4'}]
        token = token[0]

        return [302, "text/plain", None,
                {'Location': f'https://moonlight.wideiptv.top/{pid}/index.fmp4.m3u8?token={token}'}]

if __name__ == '__main__':
    pass
# -*- coding: utf-8 -*-
# @Author  : Doubebly
# @Time    : 2025/3/22 21:03
import json
import sys
import requests
from lxml import etree, html
import re
sys.path.append('..')
from base.spider import Spider


class Spider(Spider):
    def getName(self):
        return "爱瓜TV"

    def init(self, extend):
        self.home_url = 'https://aigua1.com'
        self.headers = {
            "User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/114.0.0.0 Safari/537.36",
            "Referer": "https://aigua1.com/",
        }
        self.image_domain = "https://vres.wbadl.cn"  # 圖片域名

        self.default_play_url = 'https://sf1-cdn-tos.huoshanstatic.com/obj/media-fe/xgplayer_doc_video/mp4/xgplayer-demo-720p.mp4'

    def getDependence(self):
        return []

    def isVideoFormat(self, url):
        pass

    def manualVideoCheck(self):
        pass

    def homeContent(self, filter):
        result = {
            'class': [
                {'type_id': '2', 'type_name': '电视剧'},
                {'type_id': '1', 'type_name': '电影'},
                {'type_id': '3', 'type_name': '综艺'},
                {'type_id': '4', 'type_name': '动漫'},
                {'type_id': '32', 'type_name': '纪录片'}
            ],
            'filters': {
                '1': [  # 电影筛选条件
                    {'name': '剧情', 'key': 'class', 'value': [
                        {'n': '全部', 'v': '0'},
                        {'n': '魔幻', 'v': '179'},
                        {'n': '动作', 'v': '154'},
                        {'n': '科幻', 'v': '159'},
                        {'n': '惊悚', 'v': '156'},
                        {'n': '犯罪', 'v': '157'},
                        {'n': '剧情', 'v': '161'},
                        {'n': '悬疑', 'v': '160'},
                        {'n': '奇幻', 'v': '226'},
                        {'n': '爱情', 'v': '155'},
                        {'n': '战争', 'v': '164'},
                        {'n': '恐怖', 'v': '169'},
                        {'n': '喜剧', 'v': '153'},
                        {'n': '冒险', 'v': '280'},
                        {'n': '灾难', 'v': '281'},
                        {'n': '歌舞', 'v': '282'},
                        {'n': '动画', 'v': '283'},
                        {'n': '经典', 'v': '284'},
                        {'n': '同性', 'v': '285'},
                        {'n': '网络电影', 'v': '286'},
                        {'n': '其他', 'v': '178'}
                    ]},
                    {'name': '地区', 'key': 'area', 'value': [
                        {'n': '全部', 'v': '0'},
                        {'n': '大陆', 'v': '18'},
                        {'n': '日本', 'v': '24'},
                        {'n': '香港', 'v': '20'},
                        {'n': '韩国', 'v': '21'},
                        {'n': '台湾', 'v': '23'},
                        {'n': '英国', 'v': '22'},
                        {'n': '东南亚', 'v': '29'},
                        {'n': '欧美', 'v': '19'},
                        {'n': '其它', 'v': '30'}
                    ]},
                    {'name': '年份', 'key': 'year', 'value': [
                        {'n': '全部', 'v': '0'},
                        {'n': '2025', 'v': '131'},
                        {'n': '2024', 'v': '130'},
                        {'n': '2023', 'v': '129'},
                        {'n': '2022', 'v': '21'},
                        {'n': '2021', 'v': '22'},
                        {'n': '2020', 'v': '23'},
                        {'n': '2019', 'v': '24'},
                        {'n': '2018', 'v': '25'},
                        {'n': '2017', 'v': '26'},
                        {'n': '2016', 'v': '27'},
                        {'n': '2015', 'v': '28'},
                        {'n': '2014', 'v': '29'},
                        {'n': '2013', 'v': '30'},
                        {'n': '2012', 'v': '31'},
                        {'n': '2011', 'v': '32'},
                        {'n': '2010', 'v': '33'},
                        {'n': '2009', 'v': '34'},
                        {'n': '2008', 'v': '35'},
                        {'n': '更早', 'v': '127'}
                    ]},
                    {'name': '状态', 'key': 'status', 'value': [
                        {'n': '全部', 'v': '0'},
                        {'n': '完结', 'v': '1'},
                        {'n': '更新中', 'v': '2'}
                    ]},
                    {'name': '排序', 'key': 'by', 'value': [
                        {'n': '添加时间', 'v': 'new'},
                        {'n': '人气高低', 'v': 'hot'},
                        {'n': '评分高低', 'v': 'score'}
                    ]}
                ],
                '2': [  # 电视剧筛选条件
                    {'name': '剧情', 'key': 'class', 'value': [
                        {'n': '全部', 'v': '0'},
                        {'n': '短剧', 'v': '364'},
                        {'n': '偶像', 'v': '251'},
                        {'n': '爱情', 'v': '252'},
                        {'n': '言情', 'v': '253'},
                        {'n': '古装', 'v': '254'},
                        {'n': '历史', 'v': '255'},
                        {'n': '玄幻', 'v': '256'},
                        {'n': '谍战', 'v': '257'},
                        {'n': '历险', 'v': '258'},
                        {'n': '都市', 'v': '259'},
                        {'n': '科幻', 'v': '260'},
                        {'n': '军旅', 'v': '261'},
                        {'n': '喜剧', 'v': '262'},
                        {'n': '武侠', 'v': '263'},
                        {'n': '江湖', 'v': '264'},
                        {'n': '罪案', 'v': '265'},
                        {'n': '青春', 'v': '266'},
                        {'n': '家庭', 'v': '267'},
                        {'n': '战争', 'v': '268'},
                        {'n': '悬疑', 'v': '269'},
                        {'n': '穿越', 'v': '270'},
                        {'n': '宫廷', 'v': '271'},
                        {'n': '神话', 'v': '272'},
                        {'n': '商战', 'v': '273'},
                        {'n': '警匪', 'v': '274'},
                        {'n': '动作', 'v': '275'},
                        {'n': '惊悚', 'v': '276'},
                        {'n': '剧情', 'v': '277'},
                        {'n': '同性', 'v': '278'},
                        {'n': '奇幻', 'v': '279'},
                        {'n': '其他', 'v': '231'}
                    ]},
                    {'name': '地区', 'key': 'area', 'value': [
                        {'n': '全部', 'v': '0'},
                        {'n': '大陆', 'v': '18'},
                        {'n': '日本', 'v': '24'},
                        {'n': '香港', 'v': '20'},
                        {'n': '韩国', 'v': '21'},
                        {'n': '台湾', 'v': '23'},
                        {'n': '英国', 'v': '22'},
                        {'n': '东南亚', 'v': '29'},
                        {'n': '欧美', 'v': '19'},
                        {'n': '其它', 'v': '30'}
                    ]},
                    {'name': '年份', 'key': 'year', 'value': [
                        {'n': '全部', 'v': '0'},
                        {'n': '2025', 'v': '131'},
                        {'n': '2024', 'v': '130'},
                        {'n': '2023', 'v': '129'},
                        {'n': '2022', 'v': '21'},
                        {'n': '2021', 'v': '22'},
                        {'n': '2020', 'v': '23'},
                        {'n': '2019', 'v': '24'},
                        {'n': '2018', 'v': '25'},
                        {'n': '2017', 'v': '26'},
                        {'n': '2016', 'v': '27'},
                        {'n': '2015', 'v': '28'},
                        {'n': '2014', 'v': '29'},
                        {'n': '2013', 'v': '30'},
                        {'n': '2012', 'v': '31'},
                        {'n': '2011', 'v': '32'},
                        {'n': '2010', 'v': '33'},
                        {'n': '2009', 'v': '34'},
                        {'n': '2008', 'v': '35'},
                        {'n': '更早', 'v': '127'}
                    ]},
                    {'name': '状态', 'key': 'status', 'value': [
                        {'n': '全部', 'v': '0'},
                        {'n': '完结', 'v': '1'},
                        {'n': '更新中', 'v': '2'}
                    ]},
                    {'name': '排序', 'key': 'by', 'value': [
                        {'n': '添加时间', 'v': 'new'},
                        {'n': '人气高低', 'v': 'hot'},
                        {'n': '评分高低', 'v': 'score'}
                    ]}
                ],
                '3': [  # 综艺筛选条件
                    {'name': '类型', 'key': 'class', 'value': [
                        {'n': '全部', 'v': '0'},
                        {'n': '生活', 'v': '229'},
                        {'n': '脱口秀', 'v': '228'},
                        {'n': '真人秀', 'v': '227'},
                        {'n': '访谈', 'v': '168'},
                        {'n': '选秀', 'v': '287'},
                        {'n': '网综', 'v': '288'},
                        {'n': '搞笑', 'v': '289'},
                        {'n': '竞技', 'v': '290'},
                        {'n': '情感', 'v': '291'},
                        {'n': '演唱会', 'v': '292'},
                        {'n': '晚会', 'v': '293'},
                        {'n': '其他', 'v': '232'}
                    ]},
                    {'name': '地区', 'key': 'area', 'value': [
                        {'n': '全部', 'v': '0'},
                        {'n': '大陆', 'v': '18'},
                        {'n': '日本', 'v': '24'},
                        {'n': '香港', 'v': '20'},
                        {'n': '韩国', 'v': '21'},
                        {'n': '台湾', 'v': '23'},
                        {'n': '东南亚', 'v': '29'},
                        {'n': '欧美', 'v': '19'},
                        {'n': '其它', 'v': '30'}
                    ]},
                    {'name': '年份', 'key': 'year', 'value': [
                        {'n': '全部', 'v': '0'},
                        {'n': '2025', 'v': '131'},
                        {'n': '2024', 'v': '130'},
                        {'n': '2023', 'v': '129'},
                        {'n': '2022', 'v': '21'},
                        {'n': '2021', 'v': '22'},
                        {'n': '2020', 'v': '23'},
                        {'n': '2019', 'v': '24'},
                        {'n': '2018', 'v': '25'},
                        {'n': '2017', 'v': '26'},
                        {'n': '2016', 'v': '27'},
                        {'n': '2015', 'v': '28'},
                        {'n': '2014', 'v': '29'},
                        {'n': '2013', 'v': '30'},
                        {'n': '2012', 'v': '31'},
                        {'n': '2011', 'v': '32'},
                        {'n': '2010', 'v': '33'},
                        {'n': '2009', 'v': '34'},
                        {'n': '2008', 'v': '35'},
                        {'n': '更早', 'v': '127'}
                    ]},
                    {'name': '状态', 'key': 'status', 'value': [
                        {'n': '全部', 'v': '0'},
                        {'n': '完结', 'v': '1'},
                        {'n': '更新中', 'v': '2'}
                    ]},
                    {'name': '排序', 'key': 'by', 'value': [
                        {'n': '添加时间', 'v': 'new'},
                        {'n': '人气高低', 'v': 'hot'},
                        {'n': '评分高低', 'v': 'score'}
                    ]}
                ],
                '4': [  # 动漫筛选条件
                    {'name': '类型', 'key': 'class', 'value': [
                        {'n': '全部', 'v': '0'},
                        {'n': '儿童', 'v': '363'},
                        {'n': '格斗', 'v': '167'},
                        {'n': '热血', 'v': '165'},
                        {'n': '机战', 'v': '166'},
                        {'n': '少女', 'v': '294'},
                        {'n': '竞技', 'v': '295'},
                        {'n': '科幻', 'v': '296'},
                        {'n': '爆笑', 'v': '297'},
                        {'n': '推理', 'v': '298'},
                        {'n': '魔幻', 'v': '299'},
                        {'n': '冒险', 'v': '300'},
                        {'n': '恋爱', 'v': '301'},
                        {'n': '校园', 'v': '302'},
                        {'n': '治愈', 'v': '303'},
                        {'n': '泡面', 'v': '304'},
                        {'n': '穿越', 'v': '305'},
                        {'n': '灵异', 'v': '306'},
                        {'n': '耽美', 'v': '307'},
                        {'n': '剧场版', 'v': '308'},
                        {'n': '其他', 'v': '170'}
                    ]},
                    {'name': '地区', 'key': 'area', 'value': [
                        {'n': '全部', 'v': '0'},
                        {'n': '大陆', 'v': '18'},
                        {'n': '日本', 'v': '24'},
                        {'n': '香港', 'v': '20'},
                        {'n': '韩国', 'v': '21'},
                        {'n': '台湾', 'v': '23'},
                        {'n': '英国', 'v': '22'},
                        {'n': '东南亚', 'v': '29'},
                        {'n': '欧美', 'v': '19'},
                        {'n': '其它', 'v': '30'}
                    ]},
                    {'name': '年份', 'key': 'year', 'value': [
                        {'n': '全部', 'v': '0'},
                        {'n': '2025', 'v': '131'},
                        {'n': '2024', 'v': '130'},
                        {'n': '2023', 'v': '129'},
                        {'n': '2022', 'v': '21'},
                        {'n': '2021', 'v': '22'},
                        {'n': '2020', 'v': '23'},
                        {'n': '2019', 'v': '24'},
                        {'n': '2018', 'v': '25'},
                        {'n': '2017', 'v': '26'},
                        {'n': '2016', 'v': '27'},
                        {'n': '2015', 'v': '28'},
                        {'n': '2014', 'v': '29'},
                        {'n': '2013', 'v': '30'},
                        {'n': '2012', 'v': '31'},
                        {'n': '2011', 'v': '32'},
                        {'n': '2010', 'v': '33'},
                        {'n': '2009', 'v': '34'},
                        {'n': '2008', 'v': '35'},
                        {'n': '更早', 'v': '127'}
                    ]},
                    {'name': '状态', 'key': 'status', 'value': [
                        {'n': '全部', 'v': '0'},
                        {'n': '完结', 'v': '1'},
                        {'n': '更新中', 'v': '2'}
                    ]},
                    {'name': '排序', 'key': 'by', 'value': [
                        {'n': '添加时间', 'v': 'new'},
                        {'n': '人气高低', 'v': 'hot'},
                        {'n': '评分高低', 'v': 'score'}
                    ]}
                ],
                '32': [  # 纪录片筛选条件
                    {'name': '类型', 'key': 'class', 'value': [
                        {'n': '全部', 'v': '0'}  # HTML未提供具体类型，仅保留“全部”
                    ]},
                    {'name': '地区', 'key': 'area', 'value': [
                        {'n': '全部', 'v': '0'}  # HTML未提供具体地区，仅保留“全部”
                    ]},
                    {'name': '年份', 'key': 'year', 'value': [
                        {'n': '全部', 'v': '0'},
                        {'n': '2025', 'v': '131'},
                        {'n': '2024', 'v': '130'},
                        {'n': '2023', 'v': '129'},
                        {'n': '2022', 'v': '21'},
                        {'n': '2021', 'v': '22'},
                        {'n': '2020', 'v': '23'},
                        {'n': '2019', 'v': '24'},
                        {'n': '2018', 'v': '25'},
                        {'n': '2017', 'v': '26'},
                        {'n': '2016', 'v': '27'},
                        {'n': '2015', 'v': '28'},
                        {'n': '2014', 'v': '29'},
                        {'n': '2013', 'v': '30'},
                        {'n': '2012', 'v': '31'},
                        {'n': '2011', 'v': '32'},
                        {'n': '2010', 'v': '33'},
                        {'n': '2009', 'v': '34'},
                        {'n': '2008', 'v': '35'},
                        {'n': '更早', 'v': '127'}
                    ]},
                    {'name': '状态', 'key': 'status', 'value': [
                        {'n': '全部', 'v': '0'},
                        {'n': '完结', 'v': '1'},
                        {'n': '更新中', 'v': '2'}
                    ]},
                    {'name': '排序', 'key': 'by', 'value': [
                        {'n': '添加时间', 'v': 'new'},
                        {'n': '人气高低', 'v': 'hot'},
                        {'n': '评分高低', 'v': 'score'}
                    ]}
                ]
            }
        }
        # print(f"Debug homeContent: {result}")
        return result

    def homeVideoContent(self):
        d = []
        try:
            res = requests.get(self.home_url, headers=self.headers)
            res.encoding = 'utf-8'  # 根据实际情况设置编码
            root = etree.HTML(res.text.encode('utf-8'))
            data_list = root.xpath('//div[@class="video-box-new"]/div[@class="Movie-list"]')
            for i in data_list:
                d.append(
                    {
                        'vod_id': i.xpath('./a[@class="Movie movie-height"]/@href')[0].split('=')[-1],
                        'vod_name': i.xpath('./a[2]/text()')[0].strip(),
                        'vod_pic': i.xpath('./a[1]/img/@originalsrc')[0],
                        'vod_remarks': i.xpath('./div[@class="Movie-type02"]/div[2]/text()')[0].strip()
                    }
                )
            return {'list': d, 'parse': 0, 'jx': 0}
        except Exception as e:
            print(e)
            return {'list': d, 'parse': 0, 'jx': 0}

    def categoryContent(self, cid, page, filter, ext):
        _class = ext.get('class', '0')  # 剧情/类型
        _area = ext.get('area', '0')  # 地区
        _year = ext.get('year', '0')  # 年份
        _status = ext.get('status', '0')  # 状态
        _by = ext.get('by', 'new')  # 排序

        url = self.home_url + f'/video/refresh-cate?page_num={page}&sorttype=desc&channel_id={cid}&tag={_class}&area={_area}&year={_year}&status={_status}&sort={_by}&page_size=28'
        d = []
        try:
            res = requests.get(url, headers=self.headers)
            data_list = res.json()['data']['list']
            for i in data_list:
                d.append(
                    {
                        'vod_id': i['video_id'],
                        'vod_name': i['video_name'],
                        'vod_pic': i['cover'],
                        'vod_remarks': i['flag'],
                    }
                )
            return {'list': d, 'parse': 0, 'jx': 0}
        except Exception as e:
            print(e)
            return {'list': d, 'parse': 0, 'jx': 0}

    def detailContent(self, did):
        ids = did[0]
        video_list = []
        url = self.home_url + f'/video/detail?video_id={ids}'
        try:
            res = requests.get(url, headers=self.headers)
            root = etree.HTML(res.text.encode('utf-8'))
            # vod_play_from_list = root.xpath('//span[@class="source-item-label"]/text()')
            vod_play_from = '$$$'.join(['▹ 𝟭', '▹ 𝟮', '▹ 𝟯'])
            # 电视剧
            play_list1 = root.xpath('//ul[contains(@class, "qy-episode-num")]')
            # print(play_list1)
            # 电影
            # play_list2 = root.xpath('//ul[contains(@class, "qy-play-list")]')
            play_list2 = root.xpath('//ul[@id="srctab-1"]')
            # print(play_list2)
            vod_play_url_list = []
            if len(play_list1) > 0:
                play_list = play_list1[:-1]
                # print(play_list)

            elif len(play_list2) > 0:
                play_list = play_list2
                # print(play_list)
            else:
                play_list = []

            for i in play_list:
                name_list1 = i.xpath('.//div[@class="select-link"]/text()')
                name_list2 = i.xpath('.//span[@class="title-link"]/text()')
                name_list3 = i.xpath('./li/text()')
                # print(name_list1)
                # print(name_list2)
                # print(name_list3)
                # print(name_list1 + name_list2 + name_list3)
                name_list = name_list1 + name_list2 + name_list3
                url_list = i.xpath('./li/@data-chapter-id')
                vod_play_url_list.append(
                    '#'.join([_name.strip() + '$' + f'{ids}-{_url}' for _name, _url in zip(name_list, url_list)])
                )


            # print(vod_play_url_list*3)
            vod_play_url = '$$$'.join(vod_play_url_list*3)
            # print(vod_play_url_list)
            video_list.append({
                'type_name': '',
                'vod_id': ids,
                'vod_name': '',
                'vod_remarks': '',
                'vod_year': '',
                'vod_area': '',
                'vod_actor': '',
                'vod_director': '',
                'vod_content': '',
                'vod_play_from': vod_play_from,
                'vod_play_url': vod_play_url
            })
            return {"list": video_list, 'parse': 0, 'jx': 0}

        except Exception as e:
            print(f"Error in detailContent: {e}")
            return {'list': [], 'msg': str(e)}

    def searchContent(self, key, quick, page='1'):
        if str(page) != '1':
            return {'list': [], 'parse': 0, 'jx': 0}
        url = f'{self.home_url}/video/refresh-video?page_num=1&sorttype=desc&page_size=28&tvNum=7&sort=new&keyword={key}'
        d = []
        try:
            res = requests.get(url, headers=self.headers)
            res.encoding = 'utf-8'
            root = etree.HTML(res.text)
            data_list = root.xpath('//div[@class="SSbox"]')
            for i in data_list:
                d.append(
                    {
                        'vod_id': i.xpath('./a/@href')[0].split('=')[-1],
                        'vod_name': ''.join(i.xpath('.//span/text()')),
                        'vod_pic': i.xpath('./a/img/@originalsrc')[0],
                        'vod_remarks': i.xpath('.//div[@class="SSjgTitle"]/text()')[0],
                    }
                )
            return {'list': d, 'parse': 0, 'jx': 0}
        except Exception as e:
            print(f"Error in searchContent: {e}")
            return {'list': [], 'parse': 0, 'jx': 0}

    def playerContent(self, flag, pid, vipFlags):
        url = 'https://aigua1.com/video/play-url?videoId=230907&sourceId=0&citycode=HKG&chapterId=2916522'
        a = pid.split('-')
        videoId = a[0]
        chapterId = a[1]
        url = self.home_url + f'/video/play-url?videoId={videoId}&sourceId=0&citycode=HKG&chapterId={chapterId}'
        try:
            res = requests.get(url, headers=self.headers)
            play_url_list = res.json()['data']['urlinfo']['resource_url']
            if flag == '线路一':
                play_url = play_url_list['1']
                pass
            elif flag == '线路二':
                play_url = play_url_list['16']
            else:
                play_url = play_url_list['21']
            return {'url': play_url, 'parse': 0, 'jx': 0, 'header': self.headers}
        except Exception as e:
            print(f"Error in playerContent: {e}")
            return {'url': self.default_play_url, 'parse': 0, 'jx': 0}

    def localProxy(self, params):
        pass

    def destroy(self):
        return '正在Destroy'

    def get_data(self):
        url = self.home_url + 'https://aigua1.com/video/refresh-cate?page_num=1&sorttype=desc&channel_id=0&tag=0&area=0&year=0&page_size=28&sort=new'
        pass

if __name__ == '__main__':
    pass

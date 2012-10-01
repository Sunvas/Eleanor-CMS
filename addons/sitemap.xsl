<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="2.0" xmlns:html="http://www.w3.org/TR/REC-html40" xmlns:sitemap="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output method="html" version="1.0" encoding="UTF-8" indent="yes"/>
	<xsl:template match="/">
		<html xmlns="http://www.w3.org/1999/xhtml">
			<head>
				<title>XML Sitemap</title>
				<meta http-equiv="content-type" content="text/html; charset=utf-8" />
				<style type="text/css">
					body {
						font-family:"Lucida Grande","Lucida Sans Unicode",Tahoma,Verdana;
						font-size:13px;
					}

					#intro {
						background-color:#CFEBF7;
						border:1px #2580B2 solid;
						padding:5px 13px 5px 13px;
						margin:10px;
					}

					#intro p {
						line-height:	16.8667px;
					}

					td {
						font-size:11px;
					}

					th {
						text-align:left;
						padding-right:30px;
						font-size:11px;
					}

					tr.high {
						background-color:whitesmoke;
					}

					#footer {
						padding:2px;
						margin:10px;
						font-size:8pt;
						color:gray;
					}

					#footer a {
						color:gray;
					}

					a {
						color:black;
					}
				</style>
			</head>
			<body>
				<h1>XML Карта сайта</h1>
				<div id="intro">
					<p>
						Эта XML-карта сайта предназначена для поисковых систем, таких как <a href="http://www.google.com">Google</a>, <a href="http://yandex.ru/">Yandex</a>, <a href="http://www.rambler.ru/">Rambler</a>, <a href="http://www.bing.com/">Bing Search</a>, <a href="http://www.yahoo.com">YAHOO</a> и всех остальных.<br />
						Она была сгенерирована системой управления сайтом <a href="http://eleanor-cms.ru/">Eleanor CMS</a>. Вы можете найти больше информации про XML формат карт сайтов на сайте <a href="http://www.sitemaps.org/ru/">Sitemaps.org</a>.
					</p>
				</div>
				<div id="content">
					<table cellpadding="5">
						<tr style="border-bottom:1px black solid;">
							<th>URL</th>
							<th>Приоритет</th>
							<th>Частота изменения</th>
							<th>Последнее изменение (GMT)</th>
						</tr>
						<xsl:variable name="lower" select="'abcdefghijklmnopqrstuvwxyz'"/>
						<xsl:variable name="upper" select="'ABCDEFGHIJKLMNOPQRSTUVWXYZ'"/>
						<xsl:for-each select="sitemap:urlset/sitemap:url">
							<tr>
								<xsl:if test="position() mod 2 != 1">
									<xsl:attribute  name="class">high</xsl:attribute>
								</xsl:if>
								<td>
									<xsl:variable name="itemURL">
										<xsl:value-of select="sitemap:loc"/>
									</xsl:variable>
									<a href="{$itemURL}">
										<xsl:value-of select="sitemap:loc"/>
									</a>
								</td>
								<td>
									<xsl:value-of select="concat(sitemap:priority*100,'%')"/>
								</td>
								<td>
									<xsl:value-of select="concat(translate(substring(sitemap:changefreq, 1, 1),concat($lower, $upper),concat($upper, $lower)),substring(sitemap:changefreq, 2))"/>
								</td>
								<td>
									<xsl:value-of select="concat(substring(sitemap:lastmod,0,11),concat(' ', substring(sitemap:lastmod,12,5)))"/>
								</td>
							</tr>
						</xsl:for-each>
					</table>
				</div>
				<div id="footer">
					Generated with <a href="http://eleanor-cms.ru/" >Eleanor CMS</a>. This XSLT template by <a href="http://www.arnebrachhold.de/">Arne Brachhold</a> is released under GPL.
				</div>
			</body>
		</html>
	</xsl:template>
</xsl:stylesheet>
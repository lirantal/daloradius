"""Shared HTML report parser for isolated regression tests."""
from html.parser import HTMLParser

class Rows(HTMLParser):
    def __init__(self,text):
        super().__init__(convert_charrefs=True);self.rows=[];self.row=None;self.cell=None;self.links=[];self.footer=[];self.tfoot=False;self.feed(text)
    def handle_starttag(self,tag,attrs):
        a=dict(attrs)
        if tag=='a' and 'href' in a:self.links.append(a['href'])
        if tag=='tfoot':self.tfoot=True
        if tag=='tr':self.row=[]
        if tag=='td' and self.row is not None:self.cell=[]
    def handle_data(self,text):
        if self.cell is not None:self.cell.append(text)
        if self.tfoot:self.footer.append(text)
    def handle_endtag(self,tag):
        if tag=='td' and self.cell is not None:
            self.row.append(' '.join(''.join(self.cell).split()));self.cell=None
        if tag=='tr':
            if self.row:self.rows.append(self.row)
            self.row=None
        if tag=='tfoot':self.tfoot=False
    def projection(self):return self.rows,' '.join(''.join(self.footer).split())

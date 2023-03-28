function textToLink(text)
{
    var finalText=text.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    var urlPattern = /(http|ftp|https):\/\/[\w-]+(\.[\w-]+)+([\w.,@?^=%&amp;:\/~+#-]*[\w@?^=%&amp;\/~+#-])?/gi;
    var htmlData=finalText.replace(urlPattern, '<a target="_blank" href="$&">$&</a>');
    return htmlData;
}
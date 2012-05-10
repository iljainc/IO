using System;
using System.Data;
using System.Configuration;
using System.Collections;
using System.Web;
using System.Web.Security;
using System.Web.UI;
using System.Web.UI.WebControls;
using System.Web.UI.WebControls.WebParts;
using System.Web.UI.HtmlControls;
using OpenFlashChart;

public partial class data : System.Web.UI.Page
{
    protected void Page_Load(object sender, EventArgs e)
    {
        Graph graph = new Graph();
        graph.LegendX = new LegendX("Chart Test", 12, "#FF0000");
        graph.StepsY = 5;
        graph.MaxY = 50;
        OpenFlashChart.Charts.ChartData temp;
        temp = new OpenFlashChart.Charts.AreaHollow(2, 3, "#0000CC", 75, "Profit", 12, "#AAAAFF");
        temp.Data.Add(20);
        temp.Data.Add(30);
        temp.Data.Add(40);
        temp.Data.Add(10);
        graph.Data.Add(temp);
        Response.Clear();
        Response.Write(graph.ToString());
        Response.End();
    }
}
